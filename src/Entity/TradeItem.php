<?php

namespace App\Entity;

use App\Repository\TradeItemRepository;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=TradeItemRepository::class)
 */
class TradeItem
{
    const TYPE_DEPOSIT = -1;
    const TYPE_WITHDRAW = 1;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"trade:read"})
     */
    private $id;

    /**
     * @ORM\Column(type="smallint")
     * @Groups({"trade:read","trade:write"})
     */
    private $type;

    /**
     * @ORM\ManyToOne(targetEntity=Container::class)
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"trade:read","trade:write"})
     */
    private $container;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"trade:read","trade:write"})
     */
    private $quantity;

    /**
     * @ORM\ManyToOne(targetEntity=Trade::class, inversedBy="items")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"trade:read"})
     */
    private $trade;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getTrade(): ?Trade
    {
        return $this->trade;
    }

    public function setTrade(?Trade $trade): self
    {
        $this->trade = $trade;

        return $this;
    }
}
