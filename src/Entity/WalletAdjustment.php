<?php

namespace App\Entity;

use App\Repository\WalletAdjustmentRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=WalletAdjustmentRepository::class)
 */
class WalletAdjustment
{
    const TYPE_REFUND = 1;
    const TYPE_CREDIT = 2;
    const TYPE_DEBIT = 4;

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
     * @ORM\Column(type="smallint")
     */
    private $type;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="walletAdjustments")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     */
    private $admin;

    /**
     * @ORM\Column(type="integer")
     */
    private $amount;

    /**
     * @ORM\ManyToOne(targetEntity=Order::class)
     */
    private $from_order;

    /**
     * @ORM\ManyToOne(targetEntity=Trade::class)
     */
    private $from_trade;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $notes;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(int $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getTypeAsTxt(): string
    {
        switch ($this->type){
            case self::TYPE_REFUND:
                return 'remboursement';
                break;
            case self::TYPE_CREDIT:
                return 'crédit';
                break;
            case self::TYPE_DEBIT:
                return 'débit';
                break;
            default:
                return 'N/A';
        }
    }

    public function getBalance(): ?int
    {
        switch ($this->type){
            case self::TYPE_REFUND:
            case self::TYPE_DEBIT:
                return -1*$this->getAmount();
                break;
            default:
                return $this->getAmount();
        }
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

    public function getAdmin(): ?User
    {
        return $this->admin;
    }

    public function getOperateur(): ?string
    {
        if ($this->getAdmin()){
            return $this->getAdmin()->getUsername().' [#'.$this->getAdmin()->getId().']';
        }
        return 'SYSTEM';
    }

    public function setAdmin(?User $admin): self
    {
        $this->admin = $admin;

        return $this;
    }

    public function getAmount(): ?int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getFromOrder(): ?Order
    {
        return $this->from_order;
    }

    public function setFromOrder(?Order $from_order): self
    {
        $this->from_order = $from_order;

        return $this;
    }

    public function getFromTrade(): ?Trade
    {
        return $this->from_trade;
    }

    public function setFromTrade(?Trade $from_trade): self
    {
        $this->from_trade = $from_trade;

        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): self
    {
        $this->notes = $notes;

        return $this;
    }

    public function getExtra(): ?string
    {
        if ($this->getFromOrder()){
            return 'ORDER #'.$this->getFromOrder()->getId();
        }
        if ($this->getFromTrade()){
            return 'RESTAURANT #'.$this->getFromTrade()->getRestaurant()->getId().' '.$this->getFromTrade()->getRestaurant()->getName();
        }
        return '';
    }
}
