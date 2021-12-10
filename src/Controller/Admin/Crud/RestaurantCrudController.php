<?php

namespace App\Controller\Admin\Crud;

use App\Entity\CodeRestaurant;
use App\Entity\Restaurant;
use App\Entity\Zone;
use App\Form\ZoneType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\BatchActionDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use Endroid\QrCodeBundle\Response\QrCodeResponse;
use phpDocumentor\Reflection\Types\Integer;

class RestaurantCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Restaurant::class;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('name')
            ->add('tags')
            ->add('zone')
            ->add('mealTypes')
            ;
    }

    public function configureActions(Actions $actions): Actions
    {
        $actions = parent::configureActions($actions);
        return $actions
            ->addBatchAction(Action::new('createMissingQr', 'créer un QR si manquant')
                ->linkToCrudAction('createMissingQr')
                ->addCssClass('btn btn-secondary')
                ->setIcon('fa fa-qrcode'))
            ;
    }

    public function configureFields(string $pageName): iterable
    {
        $fields = [
            IdField::new('id')->hideOnForm(),
            ImageField::new('image')
                ->setBasePath($this->getParameter('app.path.restaurant_images'))
                ->setUploadDir('/public'.$this->getParameter('app.path.restaurant_images'))
                ->setUploadedFileNamePattern("[year][month][contenthash]-[slug].[extension]"),
            TextField::new('name','nom'),
            TextField::new('formatted_address','adresse'),
            AssociationField::new('zone')->hideOnForm(),
            BooleanField::new('featured','Mis en avant'),
            BooleanField::new('show_on_map','visible sur la carte'),
            TelephoneField::new('phone','telephone')->hideOnIndex(),
            BooleanField::new('hasValidCode','Qr code valide')->onlyOnIndex(),
            TextField::new('website')->onlyWhenUpdating(),
            AssociationField::new('tags')->hideOnIndex(),
            AssociationField::new('mealTypes')->hideOnIndex()
        ];

        if ($this->isGranted('ROLE_SUPER_ADMIN')){
            $fields[] = AssociationField::new('zone')->onlyWhenUpdating();
        }

        return $fields;
    }

    public function createMissingQr(BatchActionDto $batchActionDto)
    {
        $entityManager = $this->getDoctrine()->getManagerForClass($batchActionDto->getEntityFqcn());
        foreach ($batchActionDto->getEntityIds() as $id) {
            /** @var Restaurant $restaurant */
            $restaurant = $entityManager->find(Restaurant::class,$id);
            if (!$restaurant->hasValidCode()){
                $qr = new CodeRestaurant();
                $qr->setRestaurant($restaurant);
                $qr->setEnabled(true);
                $entityManager->persist($qr);
            }
        }
        $entityManager->flush();
        return $this->redirect($batchActionDto->getReferrerUrl());
    }

}
