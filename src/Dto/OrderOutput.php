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
    public $id;

    /**
     * @var $created_at \DateTime
     * @Groups({"order:read","order:write"})
     */
    public $created_at;

    /**
     * @var $amount float
     * @Groups({"order:read","order:write"})
     */
    public $amount;

    /**
     * @var $status string
     * @Groups({"order:read","order:write"})
     */
    public $status;

    /**
     * @var $state string
     * @Groups({"order:read","order:write"})
     */
    public $state;

    /**
     * @var $user User
     * @Groups({"order:read","order:write"})
     */
    public $user;

    /**
     * @var $form_token string
     * @Groups({"order:read","order:write"})
     */
    public $form_token;
}