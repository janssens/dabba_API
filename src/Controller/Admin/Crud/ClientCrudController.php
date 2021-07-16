<?php

namespace App\Controller\Admin\Crud;

use App\Entity\Client;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ClientCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Client::class;
    }

    public function createEntity(string $entityFqcn)
    {
        return Client::create("new client ".date('Y-m-d H:i:s'));
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('id')->hideOnForm(),
            TextField::new('name'),
            TextField::new('secret'),
            TextField::new('redirect'),
            BooleanField::new('active'),
        ];
    }

}
