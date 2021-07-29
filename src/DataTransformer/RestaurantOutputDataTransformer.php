<?php
// src/DataTransformer/RestaurantOutputDataTransformer.php

namespace App\DataTransformer;

use ApiPlatform\Core\DataTransformer\DataTransformerInterface;
use App\Dto\OrderOutput;
use App\Dto\RestaurantOutput;
use App\Entity\Order;
use App\Entity\Restaurant;
use App\Entity\User;
use App\Service\SystemPay;
use Symfony\Component\Security\Core\Security;

final class RestaurantOutputDataTransformer implements DataTransformerInterface
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * {@inheritdoc}
     * @var $data Restaurant
     */
    public function transform($data, string $to, array $context = [])
    {
        $output = new RestaurantOutput();

        $output->id = $data->getId();
        $output->name = $data->getName();
        $output->lat = $data->getLat();
        $output->lng = $data->getLng();
        $output->zone_id = $data->getZone()->getId();
        $output->opening_hours = $data->getOpeningHours();
        $output->image = $data->getImage();
        $output->address = $data->getAddress();
        $output->tags = $data->getTags();
        $output->mealTypes = $data->getMealTypes();
        $output->website = $data->getWebsite();
        $output->phone = $data->getPhone();

        /** @var User $user */
        $user = $this->security->getUser();
        if (!$user){
            $output->is_favorite = false;
        }else{
            $output->is_favorite = $user->getRestaurants()->contains($data);
        }
        return $output;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsTransformation($data, string $to, array $context = []): bool
    {
        return RestaurantOutput::class === $to && $data instanceof Restaurant;
    }
}