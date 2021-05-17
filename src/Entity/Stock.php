<?php

namespace App\Entity;

use App\Repository\StockRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=StockRepository::class)
 */
class Stock
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $type;

    /**
     * @ORM\Column(type="integer")
     */
    private $owner;

    /**
     * @ORM\OneToMany(targetEntity=Movement::class, mappedBy="stock_from")
     */
    private $movements_from;

    /**
     * @ORM\OneToMany(targetEntity=Movement::class, mappedBy="stock_to")
     */
    private $movements_to;

    public function __construct()
    {
        $this->movements_from = new ArrayCollection();
        $this->movements_to = new ArrayCollection();
    }

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

    public function getOwner(): ?int
    {
        return $this->owner;
    }

    public function setOwner(int $owner): self
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * @return Collection|Movement[]
     */
    public function getMovementsFrom(): Collection
    {
        return $this->movements_from;
    }

    public function addMovementsFrom(Movement $movementsFrom): self
    {
        if (!$this->movements_from->contains($movementsFrom)) {
            $this->movements_from[] = $movementsFrom;
            $movementsFrom->setStockFrom($this);
        }

        return $this;
    }

    public function removeMovementsFrom(Movement $movementsFrom): self
    {
        if ($this->movements_from->removeElement($movementsFrom)) {
            // set the owning side to null (unless already changed)
            if ($movementsFrom->getStockFrom() === $this) {
                $movementsFrom->setStockFrom(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Movement[]
     */
    public function getMovementsTo(): Collection
    {
        return $this->movements_to;
    }

    public function addMovementsTo(Movement $movementsTo): self
    {
        if (!$this->movements_to->contains($movementsTo)) {
            $this->movements_to[] = $movementsTo;
            $movementsTo->setStockTo($this);
        }

        return $this;
    }

    public function removeMovementsTo(Movement $movementsTo): self
    {
        if ($this->movements_to->removeElement($movementsTo)) {
            // set the owning side to null (unless already changed)
            if ($movementsTo->getStockTo() === $this) {
                $movementsTo->setStockTo(null);
            }
        }

        return $this;
    }

    public function getTotalQty(): int
    {
        $from = $this->getMovementsFrom()->count();
        $to = $this->getMovementsTo()->count();
        return $from - $to;
    }

}
