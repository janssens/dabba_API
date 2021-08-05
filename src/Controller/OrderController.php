<?php

namespace App\Controller;

use App\Entity\Cms;
use App\Entity\HomeResponse;
use App\Entity\Movement;
use App\Entity\Order;
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
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class OrderController extends AbstractController
{

    private $system_pay_client;

    public function __construct(SystemPay $system_pay_client){
        $this->system_pay_client = $system_pay_client;
    }

    /**
     * @Route("/order/pay/{id}/{hash}",name="app_order_pay")
     */
    public function order_pay(Order $order,string $hash): Response
    {
        if ($this->system_pay_client->getOrderHash($order) != $hash){
            throw new AccessDeniedException();
        }

        return $this->render('order/pay.html.twig',[
            'public_key'=>$this->system_pay_client->getPublicKey(),
            'form_token'=>$this->system_pay_client->getTokenForOrder($order),
            'order'=>$order]
        );
    }

    /**
     * @Route("/order/payed",name="app_order_payed")
     */
    public function order_payed(): Response
    {
        return $this->render('order/payed.html.twig');
    }

}

