<?php

namespace App\Controller\Admin;

use App\Entity\Client;
use App\Entity\Cms;
use App\Entity\CodePromo;
use App\Entity\CodeRestaurant;
use App\Entity\Color;
use App\Entity\Container;
use App\Entity\ExternalWasteSave;
use App\Entity\MealType;
use App\Entity\Movement;
use App\Entity\Restaurant;
use App\Entity\Stock;
use App\Entity\Tag;
use App\Entity\User;
use App\Entity\Zone;
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
        return $this->render('admin/my-dashboard.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Dabba');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linktoDashboard('Dashboard', 'fa fa-home');

        yield MenuItem::linkToCrud('Restaurants', 'fas fa-list', Restaurant::class);
        yield MenuItem::linkToCrud('Types de restaurant', 'fas fa-tag', Tag::class);
        yield MenuItem::linkToCrud('QR Codes restaurant', 'fas fa-key', CodeRestaurant::class);
        yield MenuItem::linkToCrud('Codes promo', 'fas fa-tag', CodePromo::class);
        yield MenuItem::linkToCrud('Mouvements', 'fas fa-arrows-alt-h', Movement::class);
        yield MenuItem::linkToCrud('Stocks', 'fas fa-cubes', Stock::class);

        if ($this->isGranted('ROLE_SUPER_ADMIN')){
//            yield MenuItem::linkToRoute('Test System API', 'fas fa-key', 'system_pay_check');
            //yield MenuItem::linkToRoute('My Test', 'fas fa-', 'my_test');
            yield MenuItem::linkToCrud('API clients', 'fas fa-key', Client::class);
            yield MenuItem::linkToCrud('Contenus dynamiques', 'fas fa-edit', Cms::class);
            yield MenuItem::linkToCrud('Types de plat', 'fas fa-tag', MealType::class);
            yield MenuItem::linkToCrud('Zones', 'fas fa-map-marker-alt', Zone::class);
            yield MenuItem::linkToCrud('Couleurs', 'fas fa-palette', Color::class);
            yield MenuItem::linkToCrud('Conteneurs Dadda', 'fas fa-toolbox', Container::class);
            yield MenuItem::linkToCrud('Déchets extérieur', 'fas fa-recycle', ExternalWasteSave::class);
            yield MenuItem::linkToCrud('Utilisateurs', 'fas fa-user', User::class);
        }

    }
}
