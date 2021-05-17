<?php

namespace App\Controller;

use App\Entity\Container;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ContainerController extends AbstractFOSRestController
{

    /**
     * @Rest\Get(
     *     path = "/containers/{id}",
     *     name = "app_container_show",
     *     requirements = {"id"="\d+"}
     *     )
     * @Rest\View()
     */
    public function showAction(Container $container)
    {
        return $container;
    }

    /**
     * @Rest\Post(
     *    path = "/containers",
     *    name = "app_container_create"
     * )
     * @Rest\View(StatusCode = 201)
     * @ParamConverter("container", converter="fos_rest.request_body")
     */
    public function createAction(Container $container)
    {
        $em = $this->getDoctrine()->getManager();

        $em->persist($container);
        $em->flush();

        return $this->view($container,
            Response::HTTP_CREATED,
            ['Location' => $this->generateUrl('app_container_show',
                ['id' => $container->getId(), UrlGeneratorInterface::ABSOLUTE_URL])]);
    }

    /**
     * @Rest\Get("/containers", name="app_container_list")
     * @Rest\View()
     */
    public function listAction()
    {
        $containers = $this->getDoctrine()->getRepository(Container::class)->findAll();
        return $containers;
    }

}
