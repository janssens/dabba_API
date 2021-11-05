<?php

namespace App\Controller\Admin\Crud;

use App\Entity\CodePromo;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\BatchActionDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;

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
            ;
    }

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
            TextField::new('Zone'),
            BooleanField::new('isVerified','email validé'),
        ];
    }

}
