<?php

namespace App\Controller\Admin\Crud;

use App\Entity\Restaurant;
use App\Entity\Zone;
use App\Form\ZoneType;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class RestaurantCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Restaurant::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            ImageField::new('image')
                ->setBasePath($this->getParameter('app.path.restaurant_images'))
                ->setUploadDir('/public'.$this->getParameter('app.path.restaurant_images')),
            TextField::new('name'),
            TelephoneField::new('phone'),
            TextField::new('website'),
            AssociationField::new('tags'),
            AssociationField::new('mealTypes'),
            AssociationField::new('zone')->hideOnIndex()
        ];
    }
}
