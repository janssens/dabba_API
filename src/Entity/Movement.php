<?php

namespace App\Entity;

use App\Repository\MovementRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MovementRepository::class)
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

    public function __construct()
    {
        $this->stock_from = new ArrayCollection();
        $this->stock_to = new ArrayCollection();
    }

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

    public function getReason(): ?int
    {
        return $this->reason;
    }

    public function setReason(int $reason): self
    {
        $this->reason = $reason;

        return $this;
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

}
