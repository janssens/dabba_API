<?php

namespace App\Entity;

use App\Repository\ContainerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use JMS\Serializer\Annotation as Serializer;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Dto\TradeItemInput;

/**
 * @ApiResource(
 *     collectionOperations={"get"},
 *     itemOperations={"get"},
 *     normalizationContext={"groups"={"container:read"}},
 *     denormalizationContext={"groups"={"container:write"}}
 * )
 * @ORM\Entity(repositoryClass=ContainerRepository::class)
 * @Serializer\ExclusionPolicy("ALL")
 */
class Container extends AbstractFOSRestController
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"container:read","trade:read"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"container:read"})
     */
    private $name;

    /**
     * @ORM\Column(type="float")
     * @Groups({"container:read"})
     */
    private $price;

    /**
     * @ORM\OneToMany(targetEntity=Movement::class, mappedBy="container", orphanRemoval=true)
     */
    private $movements;

    /**
     * @ORM\Column(type="float")
     */
    private $weight_of_saved_waste;

    public function __construct()
    {
        $this->movements = new ArrayCollection();
        $this->cartItems = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->getName();
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

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @return Collection|Movement[]
     */
    public function getMovements(): Collection
    {
        return $this->movements;
    }

    public function addMovement(Movement $movement): self
    {
        if (!$this->movements->contains($movement)) {
            $this->movements[] = $movement;
            $movement->setContainer($this);
        }

        return $this;
    }

    public function removeMovement(Movement $movement): self
    {
        if ($this->movements->removeElement($movement)) {
            // set the owning side to null (unless already changed)
            if ($movement->getContainer() === $this) {
                $movement->setContainer(null);
            }
        }

        return $this;
    }

    public function getWeightOfSavedWaste(): ?float
    {
        return $this->weight_of_saved_waste;
    }

    public function setWeightOfSavedWaste(float $weight_of_saved_waste): self
    {
        $this->weight_of_saved_waste = $weight_of_saved_waste;

        return $this;
    }
}
