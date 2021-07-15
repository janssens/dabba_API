<?php

namespace App\Controller\Admin;

use App\Entity\Client;
use App\Entity\Cms;
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
            ->setTitle('Dabba');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linktoDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::linkToCrud('API client', 'fas fa-key', Client::class);
        yield MenuItem::linkToCrud('CMS blocs', 'fas fa-edit', Cms::class);
    }
}
