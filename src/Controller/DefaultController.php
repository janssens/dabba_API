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

class DefaultController extends AbstractController
{

    private $system_pay_client;

    public function __construct(SystemPay $system_pay_client){
        $this->system_pay_client = $system_pay_client;
    }

    /**
     * @Route("/",name="app_home")
     */
    public function home(): Response
    {
        return $this->render('home.html.twig');
    }

    /**
     * @Route("/system_pay_check",name="system_pay_check")
     * @IsGranted("ROLE_SUPER_ADMIN")
     */
    public function system_pay_check()
    {
        $value = md5(uniqid());
        $result = $this->system_pay_client->test($value);
        $success = false;
        $error = '';
        if (isset($result['success']) && $result['success']['value'] === $value){
            $success = true;
        }
        if (isset($result['error'])){
            $error = $result['error'];
        }
        return $this->render('admin/system_pay_check.html.twig', [
            'success' => $success,
            'error' => $error,
        ]);
    }

    /**
     * @Route("/my_test",name="my_test")
     * @IsGranted("ROLE_SUPER_ADMIN")
     */
    public function my_test()
    {
        $em = $this->getDoctrine()->getManager();
        $tag1 = $em->getRepository(Tag::class)->find(1);
        $tag2 = $em->getRepository(Tag::class)->find(2);

        /** @var Restaurant $restaurant */
        $restaurant = $em->getRepository(Restaurant::class)->find(1);
        $restaurant->addTag($tag1);
        $restaurant->addTag($tag2);

        $em->persist($restaurant);
        $em->flush();

        return $this->render('admin/my_test.html.twig');
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
    public function getCurrentUser(): User
    {
        return $this->getUser();
    }
    /**
     * @Route(
     *     "/api/cms/global_stats",
     *     name="api_global_stats",
     *     methods={"GET"},
     *     defaults={
     *          "_api_resource_class" = Cms::class,
     *          "_api_collection_operation_name" = "stat",
     *     })
     */
    public function getGlobalStats(): array
    {
        return ["avoidedWaste"=>431762,"massOfAvoidedWaste"=>4000.0];
    }

}

