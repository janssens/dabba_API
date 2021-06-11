<?php

namespace App\Entity;

use App\Repository\RestaurantRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;
use Hateoas\Configuration\Annotation as Hateoas;

/**
 * @ORM\Entity(repositoryClass=RestaurantRepository::class)
 * @Hateoas\Relation(
 *      "self",
 *      href = @Hateoas\Route(
 *          "app_restaurant_show",
 *          parameters = { "id" = "expr(object.getId())" },
 *          absolute = true
 *      )
 * )
 * @Hateoas\Relation(
 *      "modify",
 *      href = @Hateoas\Route(
 *          "app_restaurant_update",
 *          parameters = { "id" = "expr(object.getId())" },
 *          absolute = true
 *      )
 * )
 * @Hateoas\Relation (
 *      "delete",
 *      href = @Hateoas\Route(
 *          "app_restaurant_delete",
 *          parameters = { "id" = "expr(object.getId())" },
 *          absolute = true
 *      )
 * )
 * @Hateoas\Relation  (
 *     "openning_hours",
 *     embedded = @Hateoas\Embedded("expr(service('app.service.place').getOpenningHours(object.getGooglePlaceId()))"),
 * )
 * @Serializer\ExclusionPolicy ("all")
 */
class Restaurant
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Serializer\Expose()
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255,unique=true)
     * @Assert\NotBlank()
     * @Serializer\Expose()
     */
    private $name;

    /**
     * @ORM\Column(type="float")
     * @Serializer\Expose()
     */
    private $lat;

    /**
     * @ORM\Column(type="float")
     * @Serializer\Expose()
     */
    private $lng;

    /**
     * @ORM\ManyToOne(targetEntity=Zone::class, inversedBy="restaurants",cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     * @Serializer\Expose()
     */
    private $zone;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $google_place_id;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getLat(): ?float
    {
        return $this->lat;
    }

    public function setLat(float $lat): self
    {
        $this->lat = $lat;

        return $this;
    }

    public function getLng(): ?float
    {
        return $this->lng;
    }

    public function setLng(float $lng): self
    {
        $this->lng = $lng;

        return $this;
    }

    public function getZone(): ?Zone
    {
        return $this->zone;
    }

    public function setZone(?Zone $zone): self
    {
        $this->zone = $zone;

        return $this;
    }

    public function getGooglePlaceId(): ?string
    {
        return $this->google_place_id;
    }

    public function setGooglePlaceId(?string $google_place_id): self
    {
        $this->google_place_id = $google_place_id;

        return $this;
    }
}
