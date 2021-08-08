<?php

namespace App\Controller;

use App\Entity\CodeRestaurant;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class RestaurantController
 * @package App\Controller
 * @Route("/restaurant")
 */
class RestaurantController extends AbstractController
{

    /**
     * @Route("/qr/{code}",name="app_restaurant_qr")
     */
    public function register(CodeRestaurant $codeRestaurant): Response
    {
        return $this->render('restaurant/qr.html.twig', [
            'restaurant' => $codeRestaurant->getRestaurant(),
            'code' => $codeRestaurant,
        ]);
    }
}
