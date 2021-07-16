<?php

namespace App\Controller\Admin\Crud;

use App\Entity\Container;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ContainerCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Container::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('name'),
            NumberField::new('price'),
        ];
    }
}
