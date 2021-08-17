<?php
// src/Dto/PaymentTokenOutput.php

namespace App\Dto;

use Symfony\Component\Serializer\Annotation\Groups;

final class PaymentTokenOutput {
    /**
     * @var $uuid string
     * @Groups({"user:read"})
     */
    public string $uuid;

    /**
     * @var string
     * @Groups({"user:read"})
     */
    public string $pan;

    /**
     * @var integer
     * @Groups({"user:read"})
     */
    public int $expiryMonth;

    /**
     * @var integer
     * @Groups({"user:read"})
     */
    public int $expiryYear;

    /**
     * @var string
     * @Groups({"user:read"})
     */
    public string $brand;



}