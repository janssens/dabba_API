<?php
// src/EventListener/OrderChangedNotifier.php
namespace App\EventListener;

use App\Entity\CodePromo;
use App\Entity\Order;
use App\Entity\User;
use Doctrine\ORM\Event\LifecycleEventArgs;

class CodePromoListener
{
    public function preUpdate(CodePromo $codePromo,LifecycleEventArgs $eventArgs)
    {
        if ($eventArgs->hasChangedField('used_by') && $eventArgs->getNewValue('used_by')) {
            $amount = $codePromo->getAmount();
            $user = $eventArgs->getNewValue('used_by');
            $wallet = $user->getWallet();
            $em = $eventArgs->getObjectManager();
            $em->getUnitOfWork()->scheduleExtraUpdate($user, array(
                'wallet' => array($wallet, $wallet+$amount)
            ));
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
