<?php

namespace App\Controller\Admin\Crud;

use App\Entity\CodeRestaurant;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterCrudActionEvent;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Endroid\QrCodeBundle\Response\QrCodeResponse;

class CodeRestaurantCrudController extends AbstractCrudController
{
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('QR Code')
            ->setEntityLabelInPlural('QR Codes')
            ->setEntityPermission('ROLE_ADMIN');
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('restaurant')
            ;
    }

    public static function getEntityFqcn(): string
    {
        return CodeRestaurant::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        $viewQr = Action::new('viewQr', 'voir Qr', 'fa fa-qr')
            ->linkToCrudAction('viewQr');
        $actions = parent::configureActions($actions);
        return $actions->add(Crud::PAGE_INDEX, $viewQr);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('code')->hideOnForm(),
            BooleanField::new('enabled'),
            AssociationField::new('restaurant')
        ];
    }

    public function viewQr(AdminContext $context)
    {
        return $this->render('admin/crud/qr.html.twig',['code'=>$context->getEntity()->getInstance()]);
    }

}
