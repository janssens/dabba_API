<?php
// src/Dto/OrderOutput.php

namespace App\Dto;

use App\Entity\User;
use Symfony\Component\Serializer\Annotation\Groups;

final class TradeItemInput {

    /**
     * @var $quantity float
     * @Groups({"trade:write"})
     */
    public float $quantity;

    /**
     * @var $container_id integer
     * @Groups({"trade:write"})
     */
    public int $container_id;

    /**
     * @var $type integer
     * @Groups({"trade:write"})
     */
    public int $type;

}