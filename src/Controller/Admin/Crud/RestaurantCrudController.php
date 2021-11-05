<?php

namespace App\Controller\Admin\Crud;

use App\Entity\Restaurant;
use App\Entity\Zone;
use App\Form\ZoneType;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use phpDocumentor\Reflection\Types\Integer;

class RestaurantCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Restaurant::class;
    }

    public function configureFields(string $pageName): iterable
    {
        $fields = [
            IdField::new('id')->hideOnForm(),
            ImageField::new('image')
                ->setBasePath($this->getParameter('app.path.restaurant_images'))
                ->setUploadDir('/public'.$this->getParameter('app.path.restaurant_images'))
                ->setUploadedFileNamePattern("[year][month][contenthash]-[slug].[extension]"),
            TextField::new('name'),
            NumberField::new('lat')->hideOnForm(),
            NumberField::new('lng')->hideOnForm(),
            TextField::new('formatted_address'),
            TelephoneField::new('phone'),
            TextField::new('website')->onlyWhenUpdating(),
            AssociationField::new('tags'),
            AssociationField::new('mealTypes')
        ];

        if ($this->isGranted('ROLE_SUPER_ADMIN')){
            $fields[] = AssociationField::new('zone')->onlyWhenUpdating();
        }

        return $fields;
    }

}
