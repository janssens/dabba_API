<?php
// src/EventListener/TradetListener.php
namespace App\EventListener;

use App\Entity\CodeRestaurant;
use App\Entity\Container;
use App\Entity\Movement;
use App\Entity\Stock;
use App\Entity\TradeItem;
use App\Exception\NotEnoughStock;
use App\Exception\WrongInputException;
use Doctrine\ORM\Event\LifecycleEventArgs;
use App\Entity\Trade;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class TradetListener
{
    public function prePersist(Trade $trade,LifecycleEventArgs $eventArgs)
    {
        //check if it's a valid trade
        $trade_balance = $trade->getBalance();
        if ($trade->getUser()->getWallet()+$trade_balance<=0){
            throw new \Exception('Inadequate wallet funding',400);
        }
//        if (!$trade->getUser()->canGive($trade->getItemsAsArray(TradeItem::TYPE_DEPOSIT))){
//            throw new NotEnoughStock('User has not enough containers to hand back.');
//        }
    }

    public function postPersist(Trade $trade,LifecycleEventArgs $eventArgs)
    {
        //create needed movements
        $em = $eventArgs->getEntityManager();
        if (!$trade->getUser()->getStock()){ //missing stock
            $stock_user = new Stock();
            $stock_user->setType(Stock::TYPE_USER);
            $stock_user->setUser($trade->getUser());
            $trade->getUser()->setStock($stock_user);
            $em->persist($stock_user);
        }
        if (!$trade->getRestaurant()->getStock()){ //missing stock
            $stock_restaurant = new Stock();
            $stock_restaurant->setType(Stock::TYPE_RESTAURANT);
            $stock_restaurant->setRestaurant($trade->getRestaurant());
            $trade->getRestaurant()->setStock($stock_restaurant);
            $em->persist($stock_restaurant);
        }
        $exchanged_qyt = [];
        $returned_qty = [];
        $withdrawn_qty = [];
        $user_containers = $trade->getUser()->getContainers();
        foreach ($trade->getItemsAsArray(TradeItem::TYPE_DEPOSIT) as $container_id => $qty) {
            $returned_qty[$container_id] = -$qty;
        }
        foreach ($trade->getItemsAsArray(TradeItem::TYPE_WITHDRAW) as $container_id => $qty) {
            $withdrawn_qty[$container_id] = $qty;
        }
        foreach ($trade->getItemsAsArray() as $container_id => $v){
            $container = $em->getRepository(Container::class)->find($container_id);
            if (!isset($user_containers[$container_id])){
                $user_containers[$container_id] = 0;
            }
            if (!isset($returned_qty[$container_id])){
                $returned_qty[$container_id] = 0;
            }
            if (!isset($withdrawn_qty[$container_id])){
                $withdrawn_qty[$container_id] = 0;
            }
            $exchanged_qyt[$container_id] = min($returned_qty[$container_id],$withdrawn_qty[$container_id]);
            $returned_qty[$container_id] -= $exchanged_qyt[$container_id];
            $withdrawn_qty[$container_id] -= $exchanged_qyt[$container_id];
            if ($exchanged_qyt[$container_id]>0){
                $move = new Movement();
                $move->setContainer($container);
                $move->setQuantity($exchanged_qyt[$container_id]);
                $move->setStockFrom($trade->getUser()->getStock());
                $move->setStockTo($trade->getRestaurant()->getStock());
                $move->setReason(Movement::TYPE_EXCHANGE);
                $em->persist($move);
            }
            if ($user_containers[$container_id]<$returned_qty[$container_id]) { //returned more than stock
                $move = new Movement();
                $move->setContainer($container);
                $move->setQuantity($returned_qty[$container_id]-$user_containers[$container_id]);
                $move->setStockFrom(null);
                $move->setStockTo($trade->getUser()->getStock());
                $move->setReason(Movement::TYPE_INVENTORY);
                $em->persist($move);
            }
            if ($returned_qty[$container_id]>0){
                $move = new Movement();
                $move->setContainer($container);
                $move->setQuantity($returned_qty[$container_id]);
                $move->setStockFrom($trade->getUser()->getStock());
                $move->setStockTo($trade->getRestaurant()->getStock());
                $move->setReason(Movement::TYPE_RETURN);
                $em->persist($move);
            }
            if ($withdrawn_qty[$container_id]>0){
                $move = new Movement();
                $move->setContainer($container);
                $move->setQuantity($withdrawn_qty[$container_id]);
                $move->setStockFrom($trade->getRestaurant()->getStock());
                $move->setStockTo($trade->getUser()->getStock());
                $move->setReason(Movement::TYPE_BUY);
                $em->persist($move);
            }
        }

        $em->flush();
    }

}
