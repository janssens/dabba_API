<?php

namespace App\Controller\Admin\Crud;

use App\Entity\Zone;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ZoneCrudController extends AbstractCrudController
{

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Zone')
            ->setEntityLabelInPlural('Zones')
            ->setEntityPermission('ROLE_SUPER_ADMIN');
    }

    public static function getEntityFqcn(): string
    {
        return Zone::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('name','nom'),
            BooleanField::new('is_default','Est-ce la zone par defaut pour les nouveaux utilisateurs ?')
        ];
    }

}
