<?php
// src/EventListener/PaymentTokenListener.php
namespace App\EventListener;

use App\Entity\CodeRestaurant;
use App\Entity\Container;
use App\Entity\Movement;
use App\Entity\PaymentToken;
use App\Entity\Restaurant;
use App\Entity\Stock;
use App\Entity\TradeItem;
use App\Entity\Transaction;
use App\Exception\NotEnoughCredit;
use App\Exception\NotEnoughStock;
use App\Service\SystemPay;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class PaymentTokenListener
{

    private $systemPay;

    public function __construct(SystemPay $systemPay){
        $this->systemPay = $systemPay;
    }

    public function postRemove(PaymentToken $paymentToken,LifecycleEventArgs $eventArgs)
    {
        $this->systemPay->removeToken($paymentToken);
    }

}
