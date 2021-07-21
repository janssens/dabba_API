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

class DefaultController extends AbstractController
{

    /**
     * @Route("/",name="app_home")
     */
    public function home(): Response
    {
        return $this->render('home.html.twig');
    }

    /**
     * @Route(
     *     "/api/users/current",
     *     name="api_current_user",
     *     methods={"GET"},
     *     defaults={
     *          "_api_resource_class" = User::class,
     *          "_api_collection_operation_name" = "current_user",
     *     })
     */
    public function __invoke(): User
    {
        return $this->getUser();
    }

}

