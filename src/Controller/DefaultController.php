<?php

namespace App\Controller;

use App\Entity\Cms;
use App\Entity\HomeResponse;
use App\Entity\Movement;
use App\Entity\Restaurant;
use App\Entity\User;
use App\Entity\Zone;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use http\Header;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class DefaultController extends AbstractFOSRestController
{

    /**
     * @Route("/",name="app_home")
     */
    public function home(): Response
    {
        return $this->render('home.html.twig');
    }

    /**
     * @Rest\Get(
     *     path = "/api/home",
     *     name = "api_home"
     *     )
     * @Rest\View(StatusCode = 200)
     */
    public function api_home()
    {
        $em = $this->getDoctrine()->getManager();

        $users_count = $em->getRepository(User::class)->number();
        $restaurants_count = $em->getRepository(Restaurant::class)->number();
        $zones_count = $em->getRepository(Zone::class)->number();
        $waste_avoided = $em->getRepository(Movement::class)->countAvoidedWaste();

        $cms = $em->getRepository(Cms::class)->findAll();

        return new HomeResponse($cms,$this->getUser(),['waste_avoided' => $waste_avoided,'users_count' => $users_count, 'zone_count'=> $zones_count,'restaurants_count' => $restaurants_count]);
    }
}

