<?php
// src/EventListener/OrderChangedNotifier.php
namespace App\EventListener;

use App\Entity\Order;
use App\Entity\Transaction;
use App\Entity\User;
use App\Entity\WalletAdjustment;
use Doctrine\ORM\Event\LifecycleEventArgs;

class OrderChangedListener
{
    private $walletAdjusments = [];

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

            $wa = new WalletAdjustment();
            $wa->setUser($user);
            $wa->setType(WalletAdjustment::TYPE_CREDIT);
            $wa->setAmount(intval($order->getAmount()));
            $wa->setCreatedAt(new \DateTimeImmutable());
            $wa->setNotes($order->getTransactionsAsTxt());
            $this->walletAdjusments[] = $wa;
        }
    }

    //https://stackoverflow.com/questions/44640879/symfony-doctrine-log-changes-in-a-table-with-an-event-listener
    public function postUpdate(Order $order,LifecycleEventArgs $eventArgs)
    {
        if (! empty($this->walletAdjusments)) {
            $em = $eventArgs->getEntityManager();

            foreach ($this->walletAdjusments as $walletAdjusment) {
                $walletAdjusment->setFromOrder($order);
                $em->persist($walletAdjusment);
            }

            $this->walletAdjusments = [];
            $em->flush();
        }
    }

}
