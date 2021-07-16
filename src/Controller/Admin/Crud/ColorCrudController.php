<?php

namespace App\Controller\Admin\Crud;

use App\Entity\Color;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ColorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ColorCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Color::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('name'),
            ColorField::new('code'),
        ];
    }

}
