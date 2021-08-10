<?php
// src/DataTransformer/RestaurantOutputDataTransformer.php

namespace App\DataTransformer;

use ApiPlatform\Core\DataTransformer\DataTransformerInterface;
use App\Dto\TradeInput;
use App\Entity\CodeRestaurant;
use App\Entity\Container;
use App\Entity\Trade;
use App\Entity\TradeItem;
use App\Exception\BadRequest;
use App\Exception\NotFound;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

final class TradeInputDataTransformer implements DataTransformerInterface
{
    private $security;
    private $entityManager;

    public function __construct(Security $security,EntityManagerInterface $entityManager)
    {
        $this->security = $security;
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     * @var $data TradeInput
     */
    public function transform($data, string $to, array $context = [])
    {
        $output = new Trade();

        /** @var CodeRestaurant $code */
        $code = $this->entityManager->getRepository(CodeRestaurant::class)->findOneBy(array('code'=>trim($data->code_from_qr)));
        if (!$code){
            throw new NotFound('No restaurant found for this code');
        }else{
            if (!$code->getEnabled()){
                throw new BadRequest('This code is no longer valid');
            }
            $output->setRestaurant($code->getRestaurant());
        }
        $output->setUser($this->security->getUser());

        foreach ($data->items as $item){
            if (isset($item['type'])&&isset($item['container_id'])&&isset($item['quantity'])){
                $new_item = new TradeItem();
                $container = $this->entityManager->getRepository(Container::class)->find($item['container_id']);
                if (!$container){
                    throw new NotFound('no container found for id #'.$item['container_id']);
                }
                $new_item->setContainer($container);
                if ($item['quantity']>0){
                    $new_item->setQuantity($item['quantity']);
                }else{
                    throw new BadRequest('quantity must be gt 0');
                }
                switch ($item['type']){
                    case "DEPOSIT":
                        $new_item->setType(TradeItem::TYPE_DEPOSIT);
                        break;
                    case "WITHDRAW":
                        $new_item->setType(TradeItem::TYPE_WITHDRAW);
                        break;
                    default:
                        throw new BadRequest('Type can be DEPOSIT or WITHDRAW');
                }
                //$this->entityManager->persist($item);
                $output->addItem($new_item);
            }else{
                throw new BadRequest('Missing param(s) for item : '.json_encode($item),422);
            }
        }

        return $output;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsTransformation($data, string $to, array $context = []): bool
    {
        if ($data instanceof Trade) {
            return false;
        }
        return Trade::class === $to && null !== ($context['input']['class'] ?? null);
    }
}