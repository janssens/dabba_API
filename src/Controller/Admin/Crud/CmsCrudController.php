<?php

namespace App\Controller\Admin\Crud;

use App\Entity\Cms;
use App\Entity\Color;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ColorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use Ramsey\Uuid\Type\Integer;

class CmsCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Cms::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Contenu dynamique')
            ->setEntityLabelInPlural('Contenus dynamiques')
            ->setEntityPermission('ROLE_ADMIN');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            AssociationField::new('zone'),
            ChoiceField::new('category')->setChoices(['home'=>Cms::CATEGORY_HOME,'mes dabbas'=>Cms::CATEGORY_MY_DABBA]),
            IntegerField::new('position'),
            DateField::new('from_date'),
            DateField::new('to_date'),
            ChoiceField::new('format')->setChoices(['small'=>Cms::FORMAT_SMALL,'full'=>Cms::FORMAT_FULL]),
            BooleanField::new('is_public'),
            TextField::new('title'),
            TextField::new('subtitle'),
            TextField::new('content'),
            AssociationField::new('textColor')->hideOnIndex(),
            AssociationField::new('backgroundColor')->hideOnIndex(),
            TextField::new('button_label'),
            TextField::new('url'),
            ImageField::new('image')
                ->setBasePath($this->getParameter('app.path.cms_images'))
                ->setUploadDir('/public'.$this->getParameter('app.path.cms_images')),
        ];
    }
}
