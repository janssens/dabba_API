<?php

namespace App\Controller;

use App\Entity\Cms;
use App\Entity\HomeResponse;
use App\Entity\Movement;
use App\Entity\Restaurant;
use App\Entity\Tag;
use App\Entity\User;
use App\Entity\Zone;
use App\Service\SystemPay;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use http\Header;
use PhpParser\Node\Expr\Array_;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

class UserController extends AbstractController
{

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
    public function getCurrentUser(): User
    {
        return $this->getUser();
    }

    /**
     * @Route(
     *     "/api/users/restaurants/add/{id}",
     *     name="api_add_to_favorite",
     *     methods={"GET"},
     *     defaults={
     *          "_api_resource_class" = User::class,
     *          "_api_collection_operation_name" = "add_to_favorite",
     *     })
     */
    public function addToFavorite(Restaurant $restaurant): User
    {
        $em = $this->getDoctrine()->getManager();
        /** @var User $current_user */
        $current_user = $this->getUser();
        $current_user->addRestaurant($restaurant);
        $em->persist($current_user);
        $em->flush();
        return $current_user;
    }

    /**
     * @Route(
     *     "/api/users/restaurants/remove/{id}",
     *     name="api_remove_from_favorite",
     *     methods={"GET"},
     *     defaults={
     *          "_api_resource_class" = User::class,
     *          "_api_collection_operation_name" = "remove_from_favorite",
     *     })
     */
    public function removeFromFavorite(Restaurant $restaurant): User
    {
        $em = $this->getDoctrine()->getManager();
        /** @var User $current_user */
        $current_user = $this->getUser();
        $current_user->removeRestaurant($restaurant);
        $em->persist($current_user);
        $em->flush();
        return $current_user;
    }

}

