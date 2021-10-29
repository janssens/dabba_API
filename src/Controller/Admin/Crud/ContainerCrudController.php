<?php

namespace App\Controller\Admin\Crud;

use App\Entity\Container;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ContainerCrudController extends AbstractCrudController
{
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Conteneur Dadda')
            ->setEntityLabelInPlural('Conteneurs Dabba')
            ->setEntityPermission('ROLE_SUPER_ADMIN');
    }

    public static function getEntityFqcn(): string
    {
        return Container::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('name','nom'),
            NumberField::new('price','prix'),
            NumberField::new('weight_of_saved_waste','Poids de l\'équivalent jetable (kg)'),
        ];
    }
}
