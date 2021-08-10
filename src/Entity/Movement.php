<?php

namespace App\Entity;

use App\Repository\MovementRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MovementRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class Movement
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $created_at;

    /**
     * @ORM\Column(type="integer")
     */
    private $reason;

    /**
     * @ORM\ManyToOne(targetEntity=Stock::class, inversedBy="movements_from")
     */
    private $stock_from;

    /**
     * @ORM\ManyToOne(targetEntity=Stock::class, inversedBy="movements_to")
     */
    private $stock_to;

    /**
     * @ORM\ManyToOne(targetEntity=Container::class, inversedBy="movements")
     * @ORM\JoinColumn(nullable=false)
     */
    private $container;

    /**
     * @ORM\Column(type="integer")
     */
    private $quantity;

    const TYPE_BUY = 1; // FROM STOCK TO USER, ONE WAY
    const TYPE_EXCHANGE = 2; // FROM USER TO STOCK, BOTH WAYS
    const TYPE_RETURN = 4; // FROM USER TO STOCK, ONE WAY
    const TYPE_INVENTORY = 8; // INVENTORY CORRECTION
    const TYPE_LOGISTICS = 16; // INTERNAL MOVEMENTS
    const TYPE_BROKEN = 32; // BROKEN
    const TYPE_LOST = 64; // LOST

    public function __construct()
    {
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
    public function setCreatedAt(): self
    {
        $this->created_at = new \DateTimeImmutable();
        return $this;
    }

    public function getReason(): ?int
    {
        return $this->reason;
    }

    public function setReason(int $reason): self
    {
        $this->reason = $reason;

        return $this;
    }

    public function getReasonTxt(): ?string
    {
        switch ($this->reason){
            case self::TYPE_BUY:
                return 'achat';
                break;
            case self::TYPE_EXCHANGE:
                return 'échange';
                break;
            case self::TYPE_RETURN:
                return 'retour';
                break;
            case self::TYPE_INVENTORY:
                return 'inventaire';
                break;
            case self::TYPE_LOGISTICS:
                return 'logistique';
                break;
            case self::TYPE_BROKEN:
                return 'cassé';
                break;
            case self::TYPE_LOST:
                return 'perdu';
                break;
            default:
                return 'N/A';
        }
    }

    public function getStockFrom(): ?Stock
    {
        return $this->stock_from;
    }

    public function setStockFrom(?Stock $stock_from): self
    {
        $this->stock_from = $stock_from;

        return $this;
    }

    public function getStockTo(): ?Stock
    {
        return $this->stock_to;
    }

    public function setStockTo(?Stock $stock_to): self
    {
        $this->stock_to = $stock_to;

        return $this;
    }

    public function getContainer(): ?Container
    {
        return $this->container;
    }

    public function setContainer(?Container $container): self
    {
        $this->container = $container;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

}
