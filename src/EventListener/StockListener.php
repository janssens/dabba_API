<?php
// src/EventListener/StockListener.php
namespace App\EventListener;

use App\Entity\Movement;
use App\Entity\Stock;
use App\Entity\Trade;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class StockListener
{
    public function preRemove(Stock $stock,LifecycleEventArgs $eventArgs): void
    {
        /** @var EntityManager $em */
        $em = $eventArgs->getEntityManager();
        foreach ($stock->getMovementsComming() as $movement){
            $movement->setStockTo(null);
            $em->persist($movement);
        }
        foreach ($stock->getMovementsLeaving() as $movement){
            $movement->setStockFrom(null);
            $em->persist($movement);
        }
        $em->flush();
    }
}
