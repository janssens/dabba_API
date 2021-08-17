<?php
// src/DataTransformer/PaymentTokenOutputDataTransformer.php

namespace App\DataTransformer;

use ApiPlatform\Core\DataTransformer\DataTransformerInterface;
use App\Dto\OrderOutput;
use App\Dto\PaymentTokenOutput;
use App\Entity\Order;
use App\Entity\PaymentToken;
use App\Service\SystemPay;
use Symfony\Component\Routing\RouterInterface;

final class PaymentTokenOutputDataTransformer implements DataTransformerInterface
{
    private $system_pay;

    public function __construct(SystemPay $systemPay)
    {
        $this->system_pay = $systemPay;
    }

    /**
     * {@inheritdoc}
     * @var $data PaymentToken
     */
    public function transform($data, string $to, array $context = [])
    {
        $output = new PaymentTokenOutput();
        $output->uuid = $data->getUuid();
        $data = $this->system_pay->getTokenInfo($data->getUuid());
        if (!$data){
            return null;
        }
        $output->pan = $data['tokenDetails']['pan'];
        $output->expiryMonth = $data['tokenDetails']['expiryMonth'];
        $output->expiryYear = $data['tokenDetails']['expiryYear'];
        $output->brand = $data['tokenDetails']['effectiveBrand'];
        return $output;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsTransformation($data, string $to, array $context = []): bool
    {
        return PaymentTokenOutput::class === $to && $data instanceof PaymentToken;
    }
}