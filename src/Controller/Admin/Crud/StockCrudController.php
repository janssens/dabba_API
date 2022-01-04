<?php

namespace App\Controller\Admin\Crud;

use App\Entity\Stock;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class StockCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Stock::class;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('id')
            ->add(ChoiceFilter::new('type','type')->setChoices([
                'Restaurant' => Stock::TYPE_RESTAURANT,
                'Utilisateur' => Stock::TYPE_USER,
                'Zone' => Stock::TYPE_ZONE
            ]))
            ->add('restaurant')
            ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::EDIT, Action::DELETE,Action::NEW)
            ;
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
        yield TextField::new('getContainersToTxt','Container')->hideOnForm();
        yield AssociationField::new('restaurant');
        yield AssociationField::new('zone');
        yield TextField::new('label');
    }

}
