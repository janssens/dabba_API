<?php

namespace App\Controller\Admin\Crud;

use App\Entity\CodePromo;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\BatchActionDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('email')
            ->add(DateTimeFilter::new('created_at','date de creation'))
            ->add('wallet')
            ->add('zone')
            ->add('roles')
            ;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->showEntityActionsAsDropdown()
            ;
    }

    public function configureActions(Actions $actions): Actions
    {
        $actions = parent::configureActions($actions);
//        $actions->addBatchAction(Action::new('makeAdminDto', 'ajouter role Admin')
//            ->linkToCrudAction('makeAdminDto')
//            ->addCssClass('btn btn-secondary')
//            ->setIcon('fa fa-user-shield'))
//        ;
//        $actions->addBatchAction(Action::new('removeAdminDto', 'supprimer role Admin')
//            ->linkToCrudAction('removeAdminDto')
//            ->addCssClass('btn btn-danger')
//            ->setIcon('fa fa-user'))
//        ;
        $make_admin_action = Action::new('makeAdmin', 'Make ADMIN','fas fa-user-shield')
            ->displayIf(static function ($entity) { /** @var User $entity */
                return !$entity->hasRoles('ROLE_ADMIN') && !$entity->hasRoles('ROLE_SUPER_ADMIN');
            })->linkToCrudAction('makeAdmin');
        $remove_admin_action = Action::new('removeAdmin', 'Remove role ADMIN','fas fa-user')
            ->displayIf(static function ($entity) { /** @var User $entity */
                return $entity->hasRoles('ROLE_ADMIN') && !$entity->hasRoles('ROLE_SUPER_ADMIN');
            })->linkToCrudAction('removeAdmin');
        $make_super_action = Action::new('makeSuperAdmin', 'Make SUPER ADMIN','fas fa-user-ninja')
            ->displayIf(static function ($entity) { /** @var User $entity */
                return $entity->hasRoles('ROLE_ADMIN') && !$entity->hasRoles('ROLE_SUPER_ADMIN');
            })->linkToCrudAction('makeSuperAdmin');
        $actions->add(Crud::PAGE_INDEX, $make_admin_action);
        $actions->add(Crud::PAGE_INDEX, $remove_admin_action);
        $actions->add(Crud::PAGE_INDEX, $make_super_action);

        $actions
            ->addBatchAction(Action::new('download', 'Exporter les utilisateurs')
                ->linkToCrudAction('csvDownload')
                ->addCssClass('btn btn-secondary')
                ->setIcon('fa fa-file-download'))
        ;

        $actions->setPermission(Action::DELETE, 'ROLE_SUPER_ADMIN');
        $actions->setPermission('makeAdmin', 'ROLE_SUPER_ADMIN');
        $actions->setPermission('makeSuperAdmin', 'ROLE_SUPER_ADMIN');
        $actions->setPermission('removeAdmin', 'ROLE_SUPER_ADMIN');
        $actions->setPermission('download', 'ROLE_SUPER_ADMIN');
        return $actions;
    }

    public function makeAdmin(AdminContext $context)
    {
        /** @var User $user */
        $user = $context->getEntity()->getInstance();
        $user->addRole('ROLE_ADMIN');
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();
        return $this->redirect($context->getReferrer());
    }

    public function removeAdmin(AdminContext $context)
    {
        /** @var User $user */
        $user = $context->getEntity()->getInstance();
        $user->removeRole('ROLE_ADMIN');
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();
        return $this->redirect($context->getReferrer());
    }

    public function makeSuperAdmin(AdminContext $context)
    {
        /** @var User $user */
        $user = $context->getEntity()->getInstance();
        $user->removeRole('ROLE_ADMIN');
        $user->addRole('ROLE_SUPER_ADMIN');
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();
        return $this->redirect($context->getReferrer());
    }


//    public function makeAdminDto(BatchActionDto $batchActionDto)
//    {
//        $entityManager = $this->getDoctrine()->getManagerForClass($batchActionDto->getEntityFqcn());
//        foreach ($batchActionDto->getEntityIds() as $id) {
//            /** @var User $user */
//            $user = $entityManager->find(User::class,$id);
//            $user->addRole('ROLE_ADMIN');
//            $entityManager->persist($user);
//        }
//        $entityManager->flush();
//        return $this->redirect($batchActionDto->getReferrerUrl());
//    }
//
//    public function removeAdminDto(BatchActionDto $batchActionDto)
//    {
//        $entityManager = $this->getDoctrine()->getManagerForClass($batchActionDto->getEntityFqcn());
//        foreach ($batchActionDto->getEntityIds() as $id) {
//            /** @var User $user */
//            $user = $entityManager->find(User::class,$id);
//            $user->removeRole('ROLE_ADMIN');
//            $entityManager->persist($user);
//        }
//        $entityManager->flush();
//        return $this->redirect($batchActionDto->getReferrerUrl());
//    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('getFullname','nom complet')->hideOnForm(),
            TextField::new('firstname','prenom')->onlyOnForms(),
            TextField::new('lastname','nom')->onlyOnForms(),
            TextField::new('password','mot de passe')->onlyWhenCreating(),
            DateTimeField::new('created_at','date de creation')->onlyOnIndex(),
            EmailField::new('email'),
            IntegerField::new('wallet','cagnotte'),
            TextField::new('getRolesList','Roles'),
            TextField::new('Zone'),
            BooleanField::new('isVerified','email validé'),
        ];
    }

    public function csvDownload(BatchActionDto $batchActionDto)
    {
        $entityManager = $this->getDoctrine()->getManagerForClass($batchActionDto->getEntityFqcn());
        $users = [];
        foreach ($batchActionDto->getEntityIds() as $id) {
            /** @var User $user */
            $user = $entityManager->find(User::class,$id);
            $users[] = [
                'id'=>$user->getId(),
                'name'=>$user->getFullname(),
                'email'=>$user->getEmail(),
                'created_at'=> $user->getCreatedAt()->format(DATE_W3C),
                'cagnotte'=> $user->getWallet(),
                'zone'=> ($user->getZone()) ? $user->getZone()->getName() : '',
            ];
        }
        $response = new StreamedResponse();
        $response->setCallback(function () use ($users) {
            $handle = fopen('php://output', 'w+');
            // Add header
            fputcsv($handle, array_keys($users[0]));
            foreach ($users as $user) {
                fputcsv($handle, array_values($user));
            }
            fclose($handle);
        });
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="users.csv"');
        return $response;
    }

}
