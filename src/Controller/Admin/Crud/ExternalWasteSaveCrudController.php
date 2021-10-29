<?php

namespace App\Controller\Admin\Crud;

use App\Entity\ExternalWasteSave;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ExternalWasteSaveCrudController extends AbstractCrudController
{
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Déchets extérieur')
            ->setEntityLabelInPlural('Déchets extérieur')
            ->setEntityPermission('ROLE_SUPER_ADMIN');
    }

    public static function getEntityFqcn(): string
    {
        return ExternalWasteSave::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            DateTimeField::new('created_at','créé le')->hideOnForm(),
            TextField::new('raison','raison'),
            AssociationField::new('container','conteneur'),
            IntegerField::new('quantity','quantité'),
        ];
    }

}
