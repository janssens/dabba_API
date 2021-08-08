<?php

namespace App\Controller\Admin;

use App\Entity\CodeRestaurant;
use App\Entity\Restaurant;
use App\Entity\Tag;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
    /**
     * @Route("/admin", name="admin")
     */
    public function index(): Response
    {
        return parent::index();
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Dabba');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linktoDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::linkToUrl('Super admin', 'fas fa-mask', $this->generateUrl('super_admin'));
        yield MenuItem::linkToCrud('The Restaurants', 'fas fa-list', Restaurant::class);
        yield MenuItem::linkToCrud('Tags restaurant', 'fas fa-tag', Tag::class);
        yield MenuItem::linkToCrud('Codes restaurant', 'fas fa-key', CodeRestaurant::class);
    }
}
