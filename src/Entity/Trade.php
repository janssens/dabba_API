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
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="trades")
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

    public function getBalance(): float
    {
        $credit = 0;
        $debit = 0;
        foreach ($this->getItems() as $item){
            if ($item->getType()===TradeItem::TYPE_WITHDRAW){
                $debit += $item->getContainer()->getPrice()*$item->getQuantity();
            }else{
                $credit += $item->getContainer()->getPrice()*$item->getQuantity();
            }
        }
        return $credit-$debit;
    }

    public function getItemsAsArray($type = null): array
    {
        $needs = array();
        /** @var TradeItem $item */
        foreach ($this->getItems() as $item){
            if (!$type || $type == $item->getType()){
                if ($item->getType()===TradeItem::TYPE_DEPOSIT){
                    if (!isset($needs[$item->getContainer()->getId()])){
                        $needs[$item->getContainer()->getId()] = -$item->getQuantity();
                    }else{
                        $needs[$item->getContainer()->getId()] -= $item->getQuantity();
                    }
                }else{
                    if (!isset($needs[$item->getContainer()->getId()])){
                        $needs[$item->getContainer()->getId()] = $item->getQuantity();
                    }else{
                        $needs[$item->getContainer()->getId()] += $item->getQuantity();
                    }
                }
            }
        }
        return $needs;
    }

    public function getItemsAsTxt($type = null): string
    {
        $lines = [];
        foreach ($this->getItems() as $item){
            if (!$type || $type == $item->getType()) {
                $line = ($item->getType() === TradeItem::TYPE_DEPOSIT) ? 'Depot' : 'Retrait';
                $line .= ' ' . $item->getQuantity() . 'x ' . $item->getContainer()->getName();
                $lines[] = $line;
            }
        }
        return implode("\n",$lines);
    }
}
