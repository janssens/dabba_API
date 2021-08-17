<?php
// src/EventListener/TransactionListener.php
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
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class TransactionListener
{

    public function postPersist(Transaction $transaction,LifecycleEventArgs $eventArgs)
    {
        //create token if asked
        $em = $eventArgs->getEntityManager();
        if ($transaction->getPaymentMethodToken()){
            $exist = $em->getRepository(PaymentToken::class)->find($transaction->getPaymentMethodToken());
            if (!$exist){
                $token = new PaymentToken($transaction->getPaymentMethodToken(),$transaction->getParent()->getUser());
                $em->persist($token);
                $em->flush();
            }
        }
    }

}
