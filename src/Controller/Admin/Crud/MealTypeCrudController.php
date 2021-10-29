<?php

namespace App\Controller\Admin\Crud;

use App\Entity\MealType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class MealTypeCrudController extends AbstractCrudController
{

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Type de plat')
            ->setEntityLabelInPlural('Types de plat')
            ->setEntityPermission('ROLE_SUPER_ADMIN');
    }

    public static function getEntityFqcn(): string
    {
        return MealType::class;
    }

    /*
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('title'),
            TextEditorField::new('description'),
        ];
    }
    */
}
