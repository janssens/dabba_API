<?php

namespace App\Controller\Admin\Crud;

use App\Entity\Movement;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class MovementCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Movement::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            DateTimeField::new('created_at')->hideOnForm(),
            TextField::new('getReasonTxt')->hideOnForm(),
            IntegerField::new('reason')->hideOnIndex(),
            AssociationField::new('container'),
            AssociationField::new('stock_from'),
            AssociationField::new('stock_to'),
            IntegerField::new('quantity'),
        ];
    }

}
