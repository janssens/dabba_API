<?php
// src/EventListener/OrderChangedNotifier.php
namespace App\EventListener;

use App\Entity\CodePromo;
use App\Entity\Order;
use App\Entity\User;
use App\Entity\WalletAdjustment;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PreFlushEventArgs;

class CodePromoListener
{
    private $walletAdjusments = [];

    public function preUpdate(CodePromo $codePromo,LifecycleEventArgs $eventArgs)
    {
        if ($eventArgs->hasChangedField('used_by') && $eventArgs->getNewValue('used_by')) {
            $amount = $codePromo->getAmount();
            /** @var User $user */
            $user = $eventArgs->getNewValue('used_by');
            $wallet = $user->getWallet();
            $em = $eventArgs->getObjectManager();
            $em->getUnitOfWork()->scheduleExtraUpdate($user, array(
                'wallet' => array($wallet, $wallet+$amount),
            ));

            $wa = new WalletAdjustment();
            $wa->setUser($user);
            $wa->setType(WalletAdjustment::TYPE_CREDIT);
            $wa->setAmount($codePromo->getAmount());
            $wa->setCreatedAt(new \DateTimeImmutable());
            $wa->setNotes('Using CODE : '.$codePromo->getCode());
            $this->walletAdjusments[] = $wa;

        }
    }

    //https://stackoverflow.com/questions/44640879/symfony-doctrine-log-changes-in-a-table-with-an-event-listener
    public function postUpdate(CodePromo $codePromo,LifecycleEventArgs $eventArgs)
    {
        if (! empty($this->walletAdjusments)) {
            $em = $eventArgs->getEntityManager();

            foreach ($this->walletAdjusments as $walletAdjusment) {
                $em->persist($walletAdjusment);
            }

            $this->walletAdjusments = [];
            $em->flush();
        }
    }

    public function prePersist(CodePromo $codePromo,LifecycleEventArgs $eventArgs)
    {
        //check if generated number is uniq
        $em = $eventArgs->getEntityManager();
        $exist = $em->getRepository(CodePromo::class)->findOneBy(array('code'=>$codePromo->getCode()));
        while ($exist) {
            $codePromo->setCode(CodePromo::makeCode());
            $exist = $em->getRepository(CodePromo::class)->findOneBy(array('code'=>$codePromo->getCode()));
        }
    }
}
