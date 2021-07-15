<?php

namespace App\Controller\Admin;

use App\Entity\Cms;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
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
            TextField::new('title'),
            DateField::new('from_date'),
            DateField::new('to_date'),
            TextEditorField::new('content'),
            TextField::new('button_label'),
            UrlField::new('url'),
            ArrayField::new('css'),
            ImageField::new('image')
                ->setBasePath($this->getParameter('app.path.cms_images'))
                ->setUploadDir('/public'.$this->getParameter('app.path.cms_images')),
        ];
    }
}
