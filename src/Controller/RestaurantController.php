<?php

namespace App\Controller;

use App\Entity\Container;
use App\Entity\Restaurant;
use App\Entity\User;
use App\Exception\ResourceValidationException;
use App\Representation\Restaurants;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;

class RestaurantController extends AbstractFOSRestController implements TokenAuthenticatedController
{

    /**
     * @Rest\Get(
     *     path = "/api/restaurants/{id}",
     *     name = "app_restaurant_show",
     *     requirements = {"id"="\d+"}
     *     )
     * @Rest\View()
     * @OA\Tag(name="restaurant")
     * @OA\Response(
     *     response=200,
     *     description="Return a restaurant",
     *     @OA\JsonContent(ref=@Model(type=Restaurant::class)),
     * )
     */
    public function showAction(Restaurant $restaurant)
    {
        return $restaurant;
    }

    /**
     * @Rest\Post(
     *    path = "/api/restaurants",
     *    name = "app_restaurant_create"
     * )
     * @OA\Post(
     *     path="/api/restaurants",
     *     summary="Create a restaurant",
     *     description="Create a new restaurant",
     * )
     * @OA\Parameter(
     *     in="body",
     *     required=true,
     *     ref=@Model(Restaurant::class)
     * ),
     * @Rest\View(StatusCode = 201)
     * @ParamConverter("restaurant", converter="fos_rest.request_body")
     * @OA\Tag(name="restaurant")
     */
    public function createAction(Restaurant $restaurant, ConstraintViolationList $violations)
    {
        if (count($violations)) {
            $message = 'The JSON sent contains invalid data. Here are the errors you need to correct: ';
            foreach ($violations as $violation) {
                $message .= sprintf("Field %s: %s ", $violation->getPropertyPath(), $violation->getMessage());
            }
            throw new ResourceValidationException($message);
        }

        $em = $this->getDoctrine()->getManager();

        $em->persist($restaurant);
        $em->flush();

        return $this->view($restaurant,
            Response::HTTP_CREATED,
            ['Location' => $this->generateUrl('app_restaurant_show',
                ['id' => $restaurant->getId(), UrlGeneratorInterface::ABSOLUTE_URL])]);
    }

    /**
     * @Rest\Get("/api/restaurants/favorites/list", name="app_restaurant_favorite_list")
     * @Rest\View(StatusCode = 200)
     * @OA\Get(
     *     path="/api/restaurants/favorites/list",
     *     summary="Returns user favorite restaurants",
     *     description="Returns the list of current user favorite restaurants",
     * )
     * @OA\Response(
     *     response=200,
     *     description="List of restaurants",
     *     @OA\JsonContent(
     *            type="array",
     *            @OA\Items(ref=@Model(type=Restaurant::class))
     *         ),
     * )
     * @OA\Tag(name="restaurant")
     */
    public function listFavoriteAction()
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user){
            throw new \Exception('Use a valid user');
        }
        return $user->getRestaurants();
    }

    /**
     * @Rest\View(StatusCode = 204)
     * @Rest\Put(
     *     path = "/api/restaurants/favorites/{id}",
     *     name = "app_restaurant_favorite_add",
     *     requirements = {"id"="\d+"}
     * )
     * @OA\Put(
     *     path="/api/restaurants/favorites/{id}",
     *     summary="Add restaurant to favorites",
     *     description="Add restaurant to user's favorites",
     * )
     * @OA\Parameter(
     *     description="ID of restaurant to add",
     *     in="path",
     *     name="id",
     *     required=true,
     *     @OA\Schema(
     *         type="integer",
     *         format="int64"
     *     )
     * ),
     * @OA\Tag(name="restaurant")
     */
    public function addFavoriteAction(Restaurant $restaurant)
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user){
            throw new \Exception('Use a valid user');
        }
        $user->addRestaurant($restaurant);
        $em = $this->getDoctrine()->getManager();

        $em->persist($user);
        $em->flush();

        return;
    }

    /**
     * @Rest\View(StatusCode = 204)
     * @Rest\Delete(
     *     path = "/api/restaurants/favorite/{id}",
     *     name = "app_restaurant_favorite_remove",
     *     requirements = {"id"="\d+"}
     * )
     * @OA\Delete(
     *     path="/api/restaurants/favorite/{id}",
     *     summary="Remove restaurant from favorites",
     *     description="Remove restaurant form user's favorites",
     * )
     * @OA\Parameter(
     *     description="ID of restaurant to remove",
     *     in="path",
     *     name="id",
     *     required=true,
     *     @OA\Schema(
     *         type="integer",
     *         format="int64"
     *     )
     * ),
     * @OA\Tag(name="restaurant")
     */
    public function removeFavoriteAction(Restaurant $restaurant)
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user){
            throw new \Exception('Use a valid user');
        }
        $user->removeRestaurant($restaurant);
        $em = $this->getDoctrine()->getManager();

        $em->persist($user);
        $em->flush();

        return;
    }

    /**
     * @Rest\Get("/api/restaurants", name="app_restaurant_list")
     * @Rest\QueryParam(
     *     name="keyword",
     *     requirements="[a-zA-Z0-9]",
     *     nullable=true,
     *     description="The keyword to search for."
     * )
     * @Rest\QueryParam(
     *     name="order",
     *     requirements="asc|desc",
     *     default="asc",
     *     description="Sort order (asc or desc)"
     * )
     * @Rest\QueryParam(
     *     name="limit",
     *     requirements="\d+",
     *     default="15",
     *     description="Max number of restaurant per page."
     * )
     * @Rest\QueryParam(
     *     name="offset",
     *     requirements="\d+",
     *     default="0",
     *     description="The pagination offset"
     * )
     * @Rest\View()
     * @OA\Tag(name="restaurant")
     */
    public function listAction(ParamFetcherInterface $paramFetcher)
    {
        $pager = $this->getDoctrine()->getRepository(Restaurant::class)->search(
            $paramFetcher->get('keyword'),
            $paramFetcher->get('order'),
            $paramFetcher->get('limit'),
            $paramFetcher->get('offset')
        );

        return new Restaurants($pager);
    }

    /**
     * @Rest\View(StatusCode = 200)
     * @Rest\Put(
     *     path = "/api/restaurants/{id}",
     *     name = "app_restaurant_update",
     *     requirements = {"id"="\d+"}
     * )
     * @ParamConverter("newRestaurant", converter="fos_rest.request_body")
     * @OA\Tag(name="restaurant")
     */
    public function updateAction(Restaurant $restaurant, Restaurant $newRestaurant, ConstraintViolationList $violations)
    {
        if (count($violations)) {
            $message = 'The JSON sent contains invalid data. Here are the errors you need to correct: ';
            foreach ($violations as $violation) {
                $message .= sprintf("Field %s: %s ", $violation->getPropertyPath(), $violation->getMessage());
            }

            throw new ResourceValidationException($message);
        }

        $restaurant->setName($newRestaurant->getName());
        $restaurant->setLat($newRestaurant->getLat());
        $restaurant->setLng($newRestaurant->getLng());

        $this->getDoctrine()->getManager()->flush();

        return $restaurant;
    }

    /**
     * @Rest\View(StatusCode = 204)
     * @Rest\Delete(
     *     path = "/api/restaurants/{id}",
     *     name = "app_restaurant_delete",
     *     requirements = {"id"="\d+"}
     * )
     * @OA\Tag(name="restaurant")
     */
    public function deleteAction(Restaurant $restaurant)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($restaurant);
        $em->flush();

        return;
    }
}
