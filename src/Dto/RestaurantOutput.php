<?php
// src/Dto/RestaurantOutput.php

namespace App\Dto;

use App\Entity\MealType;
use App\Entity\Restaurant;
use App\Entity\Tag;
use App\Entity\Zone;
use Symfony\Component\Serializer\Annotation\Groups;

final class RestaurantOutput {

    /**
     * @var integer
     * @Groups({"restaurant:read","user:read","user:write","trade:read"})
     */
    public $id;

    /**
     * @var string
     * @Groups({"restaurant:read", "restaurant:write"})
     */
    public $name;

    /**
     * @var float
     * @Groups({"restaurant:read"})
     */
    public $lat;

    /**
     * @var float
     * @Groups({"restaurant:read"})
     */
    public $lng;

    /**
     * @var integer
     * @Groups({"restaurant:read"})
     */
    public $zone_id;

    /**
     * @var array
     * @Groups({"restaurant:read"})
     */
    public $opening_hours = [];

    /**
     * @var string
     * @Groups({"restaurant:read"})
     */
    public $image;

    /**
     * @var string
     */
    public $address;

    /**
     * @var Tag[]
     * @Groups({"restaurant:read"})
     */
    public $tags;

    /**
     * @var MealType[]
     * @Groups({"restaurant:read"})
     */
    public $mealTypes;

    /**
     * @var string
     * @Groups({"restaurant:read"})
     */
    public $website;

    /**
     * @var string
     * @Groups({"restaurant:read"})
     */
    public $phone;

    /**
     * @var bool
     * @Groups({"restaurant:read"})
     */
    public $is_favorite;

}