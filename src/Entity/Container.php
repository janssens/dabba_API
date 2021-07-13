<?php

namespace App\Entity;

use App\Repository\ContainerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use OpenApi\Annotations as OA;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity(repositoryClass=ContainerRepository::class)
 * @Serializer\ExclusionPolicy("ALL")
 * @OA\Tag(name="container")
 */
class Container extends AbstractFOSRestController
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Serializer\Expose
     * @OA\Property(description="The unique identifier of the container")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Serializer\Expose
     * @OA\Property(description="The name of the container")
     */
    private $name;

    /**
     * @ORM\Column(type="float")
     * @Serializer\Expose
     * @OA\Property(description="Container price")
     */
    private $price;

    /**
     * @ORM\OneToMany(targetEntity=Movement::class, mappedBy="container", orphanRemoval=true)
     */
    private $movements;

    /**
     * @ORM\OneToMany(targetEntity=CartItem::class, mappedBy="container", orphanRemoval=true)
     */
    private $cartItems;

    public function __construct()
    {
        $this->movements = new ArrayCollection();
        $this->cartItems = new ArrayCollection();
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

    /**
     * @return Collection|CartItem[]
     */
    public function getCartItems(): Collection
    {
        return $this->cartItems;
    }

    public function addCartItem(CartItem $cartItem): self
    {
        if (!$this->cartItems->contains($cartItem)) {
            $this->cartItems[] = $cartItem;
            $cartItem->setContainer($this);
        }

        return $this;
    }

    public function removeCartItem(CartItem $cartItem): self
    {
        if ($this->cartItems->removeElement($cartItem)) {
            // set the owning side to null (unless already changed)
            if ($cartItem->getContainer() === $this) {
                $cartItem->setContainer(null);
            }
        }

        return $this;
    }
}
