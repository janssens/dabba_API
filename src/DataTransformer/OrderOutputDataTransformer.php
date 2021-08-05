<?php
// src/DataTransformer/OrderOutputDataTransformer.php

namespace App\DataTransformer;

use ApiPlatform\Core\DataTransformer\DataTransformerInterface;
use App\Dto\OrderOutput;
use App\Entity\Order;
use App\Service\SystemPay;
use Symfony\Component\Routing\RouterInterface;

final class OrderOutputDataTransformer implements DataTransformerInterface
{
    private $system_pay;
    private $router;

    public function __construct(SystemPay $systemPay,RouterInterface $router)
    {
        $this->system_pay = $systemPay;
        $this->router = $router;
    }

    /**
     * {@inheritdoc}
     * @var $data Order
     */
    public function transform($data, string $to, array $context = [])
    {
        $output = new OrderOutput();
        $output->id = $data->getId();
        $output->created_at = $data->getCreatedAt();
        $output->amount = $data->getAmount();
        $output->user = $data->getUser();
        $output->status = $data->getStatus();
        $output->state = $data->getCurrentState();
        $output->hash = $this->system_pay->getOrderHash($data);
        $output->pay_url = $this->router->generate('app_order_pay',['id'=>$data->getId(),'hash'=>$output->hash],0);
        return $output;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsTransformation($data, string $to, array $context = []): bool
    {
        return OrderOutput::class === $to && $data instanceof Order;
    }
}