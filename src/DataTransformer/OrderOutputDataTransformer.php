<?php
// src/DataTransformer/OrderOutputDataTransformer.php

namespace App\DataTransformer;

use ApiPlatform\Core\DataTransformer\DataTransformerInterface;
use App\Dto\OrderOutput;
use App\Entity\Order;
use App\Service\SystemPay;

final class OrderOutputDataTransformer implements DataTransformerInterface
{
    private $system_pay;

    public function __construct(SystemPay $systemPay)
    {
        $this->system_pay = $systemPay;
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
        $output->form_token = $this->system_pay->getTokenForOrder($data);
        $output->public_key = $this->system_pay->getPublicKey();
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