<?php
// src/EventListener/OrderChangedNotifier.php
namespace App\EventListener;

use App\Entity\CodeRestaurant;
use Doctrine\ORM\Event\LifecycleEventArgs;

class CodeRestaurantListener
{
    public function prePersist(CodeRestaurant $code,LifecycleEventArgs $eventArgs)
    {
        //check if generated number is uniq
        $em = $eventArgs->getEntityManager();
        $exist = $em->getRepository(CodeRestaurant::class)->findOneBy(array('code'=>$code->getCode()));
        while ($exist) {
            $code->setCode(CodeRestaurant::makeCode());
            $exist = $em->getRepository(CodeRestaurant::class)->findOneBy(array('code'=>$code->getCode()));
        }
    }

}
