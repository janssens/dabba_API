<?php

namespace App\Controller\Admin\Crud;

use App\Entity\Cms;
use App\Entity\Color;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
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


    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            IntegerField::new('position'),
            DateField::new('from_date'),
            DateField::new('to_date'),
            ChoiceField::new('format')->setChoices(['small'=>Cms::FORMAT_SMALL,'full'=>Cms::FORMAT_FULL]),
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
