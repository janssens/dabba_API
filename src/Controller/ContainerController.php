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
use Nelmio\ApiDocBundle\Annotation\Security;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ContainerController extends AbstractFOSRestController
{

    /**
     * @Rest\Get(
     *     path = "/api/containers/{id}",
     *     name = "app_container_show",
     *     requirements = {"id"="\d+"}
     *     )
     * @Rest\View(StatusCode = 200)
     * @OA\Response(
     *     response=200,
     *     description="Return a container",
     *     @OA\JsonContent(ref=@Model(type=Container::class)),
     * )
     * @OA\Tag(name="container")
     */
    public function showAction(Container $container)
    {
        return $container;
    }

    /**
     * @Rest\Post(
     *    path = "/api/containers",
     *    name = "app_container_create"
     * )
     * @OA\RequestBody(
     *       required=true,
     *       @OA\MediaType(
     *           mediaType="application/json",
     *           @OA\Schema(
     *               type="object",
     *               @OA\Property(
     *                   property="name",
     *                   description="Name of the container",
     *                   type="string"
     *               ),
     *               @OA\Property(
     *                   property="price",
     *                   description="Price of the container",
     *                   type="number"
     *               ),
     *           )
     *       )
     *   ),
     * @Rest\View(StatusCode = 201)
     * @ParamConverter("container", converter="fos_rest.request_body")
     * @OA\Tag(name="container")
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
     * @Rest\Get("/api/containers", name="app_container_list")
     * @Rest\View(StatusCode = 200)
     * @OA\Response(
     *     response=200,
     *     description="Returns the list of available containers",
     *     @OA\JsonContent(
     *            type="array",
     *            @OA\Items(ref=@Model(type=Container::class))
     *         ),
     * )
     * @OA\Tag(name="container")
     */
    public function listAction()
    {
        $containers = $this->getDoctrine()->getRepository(Container::class)->findAll();
        return $containers;
    }

}
