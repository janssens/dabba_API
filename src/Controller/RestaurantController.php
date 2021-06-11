<?php

namespace App\Controller;

use App\Entity\Container;
use App\Entity\Restaurant;
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

class RestaurantController extends AbstractFOSRestController implements TokenAuthenticatedController
{

    /**
     * @Rest\Get(
     *     path = "/restaurants/{id}",
     *     name = "app_restaurant_show",
     *     requirements = {"id"="\d+"}
     *     )
     * @Rest\View()
     */
    public function showAction(Restaurant $restaurant)
    {
        return $restaurant;
    }

    /**
     * @Rest\Post(
     *    path = "/restaurants",
     *    name = "app_restaurant_create"
     * )
     * @Rest\View(StatusCode = 201)
     * @ParamConverter("restaurant", converter="fos_rest.request_body")
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
     * @Rest\Get("/restaurants", name="app_restaurant_list")
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
     *     path = "/restaurants/{id}",
     *     name = "app_restaurant_update",
     *     requirements = {"id"="\d+"}
     * )
     * @ParamConverter("newRestaurant", converter="fos_rest.request_body")
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
     *     path = "/restaurants/{id}",
     *     name = "app_restaurant_delete",
     *     requirements = {"id"="\d+"}
     * )
     */
    public function deleteAction(Restaurant $restaurant)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($restaurant);
        $em->flush();

        return;
    }
}
