<?php

namespace App\Controller\Admin\Crud;

use App\Entity\CodePromo;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class CodePromoCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return CodePromo::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('code')->hideOnForm(),
            DateTimeField::new('expired_at'),
            IntegerField::new('amount'),
            DateTimeField::new('used_at'),
            AssociationField::new('used_by'),
            BooleanField::new('enabled')
        ];
    }
}
