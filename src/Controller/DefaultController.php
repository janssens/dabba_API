<?php

namespace App\Controller;

use App\Entity\Cms;
use App\Entity\ExternalWasteSave;
use App\Entity\HomeResponse;
use App\Entity\Trade;
use App\Entity\TradeItem;
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
     * @Route("/verify_success",name="app_verify_success")
     */
    public function verify_success(): Response
    {
        return $this->render('verify_success.html.twig');
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

        $message = '';

        return $this->render('admin/my_test.html.twig',["message"=>$message]);
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
        $mass = 0;
        $counter = 0;
        $em = $this->getDoctrine()->getManager();
        $trades = $em->getRepository(Trade::class)->findAll();
        /** @var Trade $trade */
        foreach ($trades as $trade){
            /** @var TradeItem $item */
            foreach ($trade->getItems() as $item){
                if ($item->getType()==TradeItem::TYPE_WITHDRAW){
                    $mass += $item->getQuantity()*$item->getContainer()->getWeightOfSavedWaste();
                    $counter += $item->getQuantity();
                }
            }
        }
        $externalWastes= $em->getRepository(ExternalWasteSave::class)->findAll();
        /** @var ExternalWasteSave $externalWaste */
        foreach ($externalWastes as $externalWaste){
            $mass += $externalWaste->getQuantity()*$externalWaste->getContainer()->getWeightOfSavedWaste();
            $counter += $externalWaste->getQuantity();
        }
        return ["avoidedWaste"=>$counter,"massOfAvoidedWaste"=>$mass];
    }

}

