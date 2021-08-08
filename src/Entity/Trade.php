<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\TradeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Dto\TradeInput;

/**
 * @ApiResource(
 *     input=TradeInput::class,
 *     collectionOperations={"post"},
 *     itemOperations={"get"},
 *     normalizationContext={"groups"={"trade:read"}},
 *     denormalizationContext={"groups"={"trade:write"}}
 * )
 * @ORM\Entity(repositoryClass=TradeRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class Trade
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"trade:read","trade:write"})
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"trade:read"})
     */
    private $created_at;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="restaurant")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"trade:read"})
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity=Restaurant::class, inversedBy="yes")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"trade:read"})
     */
    private $restaurant;

    /**
     * @ORM\OneToMany(targetEntity=TradeItem::class, mappedBy="trade", orphanRemoval=true, cascade={"persist", "remove" })
     * @Groups({"trade:read","trade:write"})
     */
    private $items;

    public function __construct()
    {
        $this->items = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    /**
     * @ORM\PrePersist
     */
    public function setCreatedAtValue(): void
    {
        $this->created_at = new \DateTimeImmutable();
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getRestaurant(): ?Restaurant
    {
        return $this->restaurant;
    }

    public function setRestaurant(?Restaurant $restaurant): self
    {
        $this->restaurant = $restaurant;

        return $this;
    }

    /**
     * @return Collection|TradeItem[]
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(TradeItem $item): self
    {
        if (!$this->items->contains($item)) {
            $this->items[] = $item;
            $item->setTrade($this);
        }

        return $this;
    }

    public function removeItem(TradeItem $item): self
    {
        if ($this->items->removeElement($item)) {
            // set the owning side to null (unless already changed)
            if ($item->getTrade() === $this) {
                $item->setTrade(null);
            }
        }

        return $this;
    }
}
