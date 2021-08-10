<?php
// src/EventListener/StockListener.php
namespace App\EventListener;

use App\Entity\Stock;
use App\Entity\Trade;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class StockListener
{
    public function postPersist(Stock $stock,LifecycleEventArgs $eventArgs){
        if ($stock->getType()==Stock::TYPE_USER){
            $user = $stock->getUser();
            if (!$user->getZone() || $user->getZone()->getIsDefault()){
                /** @var EntityManager $em */
                $em = $eventArgs->getEntityManager();
                /** @var Trade $trade */
                $trade = $em->getRepository(Trade::class)->findBy(array('user'=>$user),array('created_at'=>'ASC'),1);
                if (count($trade)>0 && $zone = $trade[0]->getRestaurant()->getZone()){
                    $user->setZone($zone);
                    $em->persist($user);
                    $em->flush();
                }else{
                    //todo : log something or notify admin
                }
            }
        }
    }
}
