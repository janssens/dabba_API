<?php
// src/Dto/OrderOutput.php

namespace App\Dto;

use App\Entity\User;
use Symfony\Component\Serializer\Annotation\Groups;

final class OrderOutput {
    /**
     * @var $id int
     * @Groups({"order:read","order:write"})
     */
    public int $id;

    /**
     * @var $created_at \DateTime
     * @Groups({"order:read","order:write"})
     */
    public \DateTime $created_at;

    /**
     * @var $amount float
     * @Groups({"order:read","order:write"})
     */
    public float $amount;

    /**
     * @var $status string
     * @Groups({"order:read","order:write"})
     */
    public string $status;

    /**
     * @var $state string
     * @Groups({"order:read","order:write"})
     */
    public string $state;

    /**
     * @var $user User
     * @Groups({"order:read","order:write"})
     */
    public User $user;

    /**
     * @var $form_token string
     * @Groups({"order:read","order:write"})
     */
    public string $form_token;
}