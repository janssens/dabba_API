<?php

namespace App\Controller\Admin;

use App\Entity\Client;
use App\Entity\Cms;
use App\Entity\Color;
use App\Entity\Container;
use App\Entity\MealType;
use App\Entity\Zone;
use App\Entity\Tag;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SuperDashboardController extends AbstractDashboardController
{
    /**
     * @Route("/admin/super", name="super_admin")
     */
    public function index(): Response
    {
        return parent::index();
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Super admin Dabba');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linktoDashboard('Super admin', 'fa fa-mask');
        yield MenuItem::linkToUrl('Admin Dashboard', 'fas fa-home', $this->generateUrl('admin'));
        yield MenuItem::linkToRoute('Test System API', 'fas fa-key', 'system_pay_check');
        yield MenuItem::linkToRoute('Test paiement', 'fas fa-credit-card', 'pay_test');
        yield MenuItem::linkToCrud('API clients', 'fas fa-key', Client::class);
        yield MenuItem::linkToCrud('CMS blocs', 'fas fa-edit', Cms::class);
        yield MenuItem::linkToCrud('Tags restaurant', 'fas fa-tag', Tag::class);
        yield MenuItem::linkToCrud('Meal types', 'fas fa-tag', MealType::class);
        yield MenuItem::linkToCrud('Zones', 'fas fa-map-marker-alt', Zone::class);
        yield MenuItem::linkToCrud('Colors', 'fas fa-palette', Color::class);
        yield MenuItem::linkToCrud('Dadda', 'fas fa-toolbox', Container::class);
    }
}
