<?php

namespace App\Controller\Admin\Crud;

use App\Entity\Stock;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class StockCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Stock::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('label')->hideOnForm();
        yield IntegerField::new('typeToString','Type')->hideOnForm();
        yield  IntegerField::new('type')->onlyWhenCreating()
                ->setFormType(ChoiceType::class)
                ->setFormTypeOptions(['choices'  => [
                    'Restaurant' => Stock::TYPE_RESTAURANT,
                    'Zone' => Stock::TYPE_ZONE
                ]]);
        yield TextField::new('getContainersToJson','Container')->hideOnForm();
        yield AssociationField::new('restaurant');
        yield AssociationField::new('zone');
        yield TextField::new('label');
    }

}
