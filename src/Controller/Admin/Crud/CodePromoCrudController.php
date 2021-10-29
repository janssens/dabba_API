<?php

namespace App\Controller\Admin\Crud;

use App\Entity\Client;
use App\Entity\CodePromo;
use App\Entity\CodeRestaurant;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\BatchActionDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CodePromoCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return CodePromo::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Code promo')
            ->setEntityLabelInPlural('Codes promo')
            ->setEntityPermission('ROLE_ADMIN');
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('code')
            ->add('amount')
            ->add(TextFilter::new('reason','raison'))
            ->add(DateTimeFilter::new('expired_at','date d\'expiration'))
            ->add(DateTimeFilter::new('used_at','date d\'utilisation'))
            ->add('used_by')
            ->add('enabled')
            ;
    }

    public function configureActions(Actions $actions): Actions
    {
        $actions = parent::configureActions($actions);
        return $actions
            ->addBatchAction(Action::new('download', 'Télécharger les codes')
                ->linkToCrudAction('csvDownload')
                ->addCssClass('btn btn-secondary')
                ->setIcon('fa fa-file-download'))
            ;
    }

    public function createEntity(string $entityFqcn)
    {
        $code = new CodePromo();
        $code->setTimes(1);
        return $code;
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance):void
    {
        if ($entityInstance->getTimes() <= 1)
            parent::persistEntity($entityManager,$entityInstance);
        else {
            $codes = [];
            for ($i=1;$i<=$entityInstance->getTimes();$i++){
                $entityInstance = clone $this->reComputeCodeIfExist($entityManager,$entityInstance,$codes);
                $new = clone $entityInstance;
                $codes[] = $new->getCode();
                $entityManager->persist($new);
            }
            $entityManager->flush();
        }
    }

    private function reComputeCodeIfExist($entityManager,CodePromo $codePromo,$codes = []) : CodePromo
    {
        $exist = $entityManager->getRepository(CodePromo::class)->findOneBy(array('code'=>$codePromo->getCode()));
        if (!$exist){
            $exist = in_array($codePromo->getCode(),$codes);
        }
        while ($exist) {
            $codePromo->setCode(CodePromo::makeCode());
            $exist = $entityManager->getRepository(CodePromo::class)->findOneBy(array('code'=>$codePromo->getCode()));
            if (!$exist){
                $exist = in_array($codePromo->getCode(),$codes);
            }
        }
        return $codePromo;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('code')->hideOnForm(),
            TextField::new('reason','raison'),
            DateTimeField::new('expired_at','date d\'expiration'),
            IntegerField::new('amount','montant'),
            IntegerField::new('times','nombre de code à génerer')->onlyWhenCreating(),
            DateTimeField::new('used_at','date d\'utilisation')->onlyWhenUpdating(),
            DateTimeField::new('used_at','date d\'utilisation')->onlyOnIndex(),
            AssociationField::new('used_by','utilisé par')->onlyWhenUpdating(),
            AssociationField::new('used_by','utilisé par')->onlyOnIndex(),
            BooleanField::new('enabled','actif')
        ];
    }

    public function csvDownload(BatchActionDto $batchActionDto)
    {
        $entityManager = $this->getDoctrine()->getManagerForClass($batchActionDto->getEntityFqcn());
        $codes = [];
        foreach ($batchActionDto->getEntityIds() as $id) {
            /** @var CodePromo $code */
            $code = $entityManager->find(CodePromo::class,$id);
            $codes[] = [
                'id'=>$code->getId(),
                'code'=>$code->getCode(),
                'amount'=>$code->getAmount(),
                'enable'=>$code->getEnabled(),
                'reason'=>$code->getReason(),
                'expired_at'=>($code->getExpiredAt()) ? $code->getExpiredAt()->format(DATE_W3C) : '',
                'used_at'=>($code->getUsedAt()) ? $code->getUsedAt()->format(DATE_W3C) : '',
                'used_by (ID)'=>($code->getUsedBy()) ? $code->getUsedBy()->getId() : '',
                'used_by (Fullname)'=>($code->getUsedBy()) ? $code->getUsedBy()->getFullname() : '',
                'used_by (email)'=>($code->getUsedBy()) ? $code->getUsedBy()->getEmail() : '',
            ];
        }
        $response = new StreamedResponse();
        $response->setCallback(function () use ($codes) {
            $handle = fopen('php://output', 'w+');
            // Add header
            fputcsv($handle, array_keys($codes[0]));
            foreach ($codes as $code) {
                fputcsv($handle, array_values($code));
            }
            fclose($handle);
        });
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="promo_codes.csv"');
        return $response;
    }
}
