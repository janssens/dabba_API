<?php

namespace App\Controller;

use App\Entity\Container;
use App\Entity\Franchise;
use App\Entity\Restaurant;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class FranchiseController extends AbstractFOSRestController
{

    /**
     * @Rest\Get(
     *     path = "/franchises/{id}",
     *     name = "app_franchise_show",
     *     requirements = {"id"="\d+"}
     *     )
     * @Rest\View()
     */
    public function showAction(Franchise $franchise)
    {
        return $franchise;
    }

    /**
     * @Rest\Post(
     *    path = "/franchises",
     *    name = "app_franchise_create"
     * )
     * @Rest\View(StatusCode = 201)
     * @ParamConverter("franchise", converter="fos_rest.request_body")
     */
    public function createAction(Franchise $franchise)
    {
        $em = $this->getDoctrine()->getManager();

        $em->persist($franchise);
        $em->flush();

        return $this->view($franchise,
            Response::HTTP_CREATED,
            ['Location' => $this->generateUrl('app_franchise_show',
                ['id' => $franchise->getId(), UrlGeneratorInterface::ABSOLUTE_URL])]);
    }

}
