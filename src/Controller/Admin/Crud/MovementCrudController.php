<?php

namespace App\Controller\Admin\Crud;

use App\Entity\Movement;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class MovementCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Movement::class;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(ChoiceFilter::new('reason','raison')->setChoices([
                'Achat' => Movement::TYPE_BUY,
                'Échange' => Movement::TYPE_EXCHANGE,
                'Retour' => Movement::TYPE_RETURN,
                'Inventaire' => Movement::TYPE_INVENTORY,
                'Perte' => Movement::TYPE_LOST,
                'Casse' => Movement::TYPE_BROKEN,
                'Logistique' => Movement::TYPE_LOGISTICS
            ]))
            ->add('created_at')
            ->add('container')
            ->add('stock_from')
            ->add('stock_to')
            ->add('quantity')
            ;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            DateTimeField::new('created_at')->hideOnForm(),
            TextField::new('getReasonTxt','raison')->hideOnForm(),
            IntegerField::new('reason','raison')->hideOnIndex()
                ->setFormType(ChoiceType::class)
                ->setFormTypeOptions(['choices'  => [
                    'Inventaire' => Movement::TYPE_INVENTORY,
                    'Perte' => Movement::TYPE_LOST,
                    'Casse' => Movement::TYPE_BROKEN,
                    'Logistique' => Movement::TYPE_LOGISTICS
                ]]),
            AssociationField::new('container'),
            AssociationField::new('stock_from'),
            AssociationField::new('stock_to'),
            IntegerField::new('quantity'),
        ];
    }

}
