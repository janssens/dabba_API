<?php
// src/EventListener/OrderChangedNotifier.php
namespace App\EventListener;

use App\Entity\Order;
use App\Entity\User;
use Doctrine\ORM\Event\LifecycleEventArgs;

class OrderChangedListener
{
    public function preUpdate(Order $order,LifecycleEventArgs $eventArgs)
    {
        if ($eventArgs->hasChangedField('state') && $eventArgs->getNewValue('state') == Order::STATE_PAID) {
            $amount = $order->getAmount();
            $user = $order->getUser();
            $wallet = $user->getWallet();
            $em = $eventArgs->getObjectManager();
            $em->getUnitOfWork()->scheduleExtraUpdate($user, array(
                'wallet' => array($wallet, $wallet+$amount)
            ));
        }
    }

}
