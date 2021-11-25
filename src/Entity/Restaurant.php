<?php

namespace App\Entity;

use App\Repository\RestaurantRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiFilter;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\RangeFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\NumericFilter;
use ApiPlatform\Core\Annotation\ApiSubresource;
use App\Dto\RestaurantOutput;

/**
 * @ApiResource(
 *     output=RestaurantOutput::class,
 *     collectionOperations={"get"},
 *     itemOperations={"get"},
 *     normalizationContext={"groups"={"restaurant:read"}},
 *     denormalizationContext={"groups"={"restaurant:write"}},
 *     attributes={"order"={"featured": "DESC","id": "ASC"}}
 * )
 * @ApiFilter(SearchFilter::class, properties={"name": "partial"})
 * @ApiFilter(RangeFilter::class, properties={"lat","lng"})
 * @ApiFilter(NumericFilter::class, properties={"zone.id"})
 * @ORM\Entity(repositoryClass=RestaurantRepository::class)
 */
class Restaurant
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255,unique=true)
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $lat;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $lng;

    /**
     * @ORM\ManyToOne(targetEntity=Zone::class, inversedBy="restaurants",cascade={"persist", "merge"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $zone;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $google_place_id;

    /**
     * @ORM\ManyToMany(targetEntity=User::class, inversedBy="restaurants")
     */
    private $fans;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $opening_hours = [];

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $image;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $formatted_address;

    /**
     * @ORM\ManyToMany(targetEntity=Tag::class, inversedBy="restaurants",cascade={"persist", "merge"})
     */
    private $tags;

    /**
     * @ORM\ManyToMany(targetEntity=MealType::class, inversedBy="restaurants",cascade={"persist", "merge"})
     */
    private $mealTypes;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $website;

    /**
     * @ORM\Column(type="string", length=17, nullable=true)
     */
    private $phone;

    /**
     * @ORM\OneToMany(targetEntity=CodeRestaurant::class, mappedBy="restaurant", orphanRemoval=true)
     */
    private $codes;

    /**
     * @ORM\OneToMany(targetEntity=Trade::class, mappedBy="restaurant", orphanRemoval=true)
     */
    private $yes;

    /**
     * @ORM\OneToOne(targetEntity=Stock::class, mappedBy="restaurant", cascade={"persist"})
     */
    private $stock;

    /**
     * @ORM\Column(type="boolean")
     */
    private $featured;

    public function __construct()
    {
        $this->fans = new ArrayCollection();
        $this->tags = new ArrayCollection();
        $this->mealTypes = new ArrayCollection();
        $this->codes = new ArrayCollection();
        $this->yes = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->getName().' ('.$this->getZone()->getName().')';
    }

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

    /**
     * @return Collection|User[]
     */
    public function getFans(): Collection
    {
        return $this->fans;
    }

    public function addFan(User $fan): self
    {
        if (!$this->fans->contains($fan)) {
            $this->fans[] = $fan;
        }

        return $this;
    }

    public function removeFan(User $fan): self
    {
        $this->fans->removeElement($fan);

        return $this;
    }

    public function getOpeningHours(): ?array
    {
        return $this->opening_hours;
    }

    public function setOpeningHours(?array $opening_hours): self
    {
        $this->opening_hours = $opening_hours;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getFormattedAddress(): ?string
    {
        return $this->formatted_address;
    }

    public function setFormattedAddress(string $address): self
    {
        $this->formatted_address = $address;

        return $this;
    }

    /**
     * @return Collection|Tag[]
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): self
    {
        if (!$this->tags->contains($tag)) {
            $this->tags[] = $tag;
            $tag->addRestaurant($this);
        }

        return $this;
    }

    public function removeTag(Tag $tag): self
    {
        if ($this->tags->removeElement($tag)) {
            $tag->removeRestaurant($this);
        }

        return $this;
    }

    /**
     * @return Collection|MealType[]
     */
    public function getMealTypes(): Collection
    {
        return $this->mealTypes;
    }

    public function addMealType(MealType $mealType): self
    {
        if (!$this->mealTypes->contains($mealType)) {
            $this->mealTypes[] = $mealType;
            $mealType->addRestaurant($this);
        }

        return $this;
    }

    public function removeMealType(MealType $mealType): self
    {
        if ($this->mealTypes->removeElement($mealType)) {
            $mealType->removeRestaurant($this);
        }

        return $this;
    }

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function setWebsite(?string $website): self
    {
        $this->website = $website;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * @return Collection|CodeRestaurant[]
     */
    public function getCodes(): Collection
    {
        return $this->codes;
    }

    public function hasValidCode(): bool
    {
        /** @var CodeRestaurant $code */
        foreach ($this->codes as $code){
            if ($code->getEnabled()){
                return true;
            }
        }
        return false;
    }

    public function addCode(CodeRestaurant $code): self
    {
        if (!$this->codes->contains($code)) {
            $this->codes[] = $code;
            $code->setRestaurant($this);
        }

        return $this;
    }

    public function removeCode(CodeRestaurant $code): self
    {
        if ($this->codes->removeElement($code)) {
            // set the owning side to null (unless already changed)
            if ($code->getRestaurant() === $this) {
                $code->setRestaurant(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Trade[]
     */
    public function getYes(): Collection
    {
        return $this->yes;
    }

    public function addYe(Trade $ye): self
    {
        if (!$this->yes->contains($ye)) {
            $this->yes[] = $ye;
            $ye->setRestaurant($this);
        }

        return $this;
    }

    public function removeYe(Trade $ye): self
    {
        if ($this->yes->removeElement($ye)) {
            // set the owning side to null (unless already changed)
            if ($ye->getRestaurant() === $this) {
                $ye->setRestaurant(null);
            }
        }

        return $this;
    }

    public function getStock(): ?Stock
    {
        return $this->stock;
    }

    public function setStock(?Stock $stock): self
    {
        // unset the owning side of the relation if necessary
        if ($stock === null && $this->stock !== null) {
            $this->stock->setRestaurant(null);
        }

        // set the owning side of the relation if necessary
        if ($stock !== null && $stock->getRestaurant() !== $this) {
            $stock->setRestaurant($this);
        }

        $this->stock = $stock;

        return $this;
    }

    public function getContainers(): ?array
    {
        if (!$this->getStock()){
            return [];
        }
        $containers = $this->getStock()->getContainers();
        foreach ($containers as $id => $container_qty){
            if ($container_qty<=0){
                unset($containers[$id]);
            }
        }
        return $containers;
    }

    public function getFeatured(): ?bool
    {
        return $this->featured;
    }

    public function setFeatured(bool $featured): self
    {
        $this->featured = $featured;

        return $this;
    }
}
