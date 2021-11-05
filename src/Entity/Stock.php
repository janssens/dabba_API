<?php

namespace App\Entity;

use App\Repository\StockRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=StockRepository::class)
 * @ORM\EntityListeners({"App\EventListener\StockListener"})
 */
class Stock
{
    const TYPE_USER = '1';
    const TYPE_RESTAURANT = '2';
    const TYPE_ZONE = '3';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="smallint")
     */
    private $type;

    /**
     * @ORM\OneToMany(targetEntity=Movement::class, mappedBy="stock_from")
     */
    private $movements_leaving;

    /**
     * @ORM\OneToMany(targetEntity=Movement::class, mappedBy="stock_to")
     */
    private $movements_comming;

    /**
     * @ORM\OneToOne(targetEntity=User::class, inversedBy="stock", cascade={"persist", "remove"})
     */
    private $user;

    /**
     * @ORM\OneToOne(targetEntity=Restaurant::class, inversedBy="stock", cascade={"persist", "remove"})
     */
    private $restaurant;

    /**
     * @ORM\ManyToOne(targetEntity=Zone::class, inversedBy="stocks")
     */
    private $zone;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $label;

    public function __construct()
    {
        $this->movements_from = new ArrayCollection();
        $this->movements_to = new ArrayCollection();
    }

    public function __toString()
    {
        switch ($this->type){
            case self::TYPE_RESTAURANT:
                return 'RESTAURANT '.$this->getRestaurant()->getName();
                break;
            case self::TYPE_USER:
                return 'UTILISATEUR #'.$this->getUser()->getId().' '.$this->getUser()->getFullname();
                break;
            case self::TYPE_ZONE:
                return 'ZONE '.$this->getZone()->getName().' #'.$this->getId().' '.$this->getLabel();
                break;
        }
    }

    public function getLinkId(){
        switch ($this->type){
            case self::TYPE_RESTAURANT:
                return "Restaurant#".$this->getRestaurant()->getId();
                break;
            case self::TYPE_USER:
                return "User#".$this->getUser()->getId();
                break;
            case self::TYPE_ZONE:
                return "Zone#".$this->getId();
                break;
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function getTypeToString(): string
    {
        switch ($this->getType()){
            case self::TYPE_USER:
                return 'USER';
                break;
            case self::TYPE_ZONE:
                return 'ZONE';
                break;
            case self::TYPE_RESTAURANT:
                return 'RESTAURANT';
                break;
            default:
                return 'N/A';
        }
    }

    public function setType(int $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return Collection|Movement[]
     */
    public function getMovementsLeaving(): Collection
    {
        if (!$this->movements_comming)
            return new ArrayCollection();
        return $this->movements_leaving;
    }

    public function addMovementsLeaving(Movement $movementsLeaving): self
    {
        if (!$this->movements_leaving->contains($movementsLeaving)) {
            $this->movements_leaving[] = $movementsLeaving;
            $movementsLeaving->setStockFrom($this);
        }
        return $this;
    }

    public function removeMovementsLeaving(Movement $movementsLeaving): self
    {
        if ($this->movements_leaving->removeElement($movementsLeaving)) {
            // set the owning side to null (unless already changed)
            if ($movementsLeaving->getStockFrom() === $this) {
                $movementsLeaving->setStockFrom(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection|Movement[]
     */
    public function getMovementsComming(): Collection
    {
        if (!$this->movements_comming)
            return new ArrayCollection();
        return $this->movements_comming;
    }

    public function addMovementsComming(Movement $movementsComming): self
    {
        if (!$this->movements_comming->contains($movementsComming)) {
            $this->movements_comming[] = $movementsComming;
            $movementsComming->setStockTo($this);
        }
        return $this;
    }

    public function removeMovementsComming(Movement $movementsComming): self
    {
        if ($this->movements_to->removeElement($movementsComming)) {
            // set the owning side to null (unless already changed)
            if ($movementsComming->getStockTo() === $this) {
                $movementsComming->setStockTo(null);
            }
        }
        return $this;
    }

    public function getContainersToJson() : string
    {
        return json_encode($this->getContainers());
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

    public function getZone(): ?Zone
    {
        return $this->zone;
    }

    public function setZone(?Zone $zone): self
    {
        $this->zone = $zone;

        return $this;
    }

    public function getContainers(): array
    {
        $return = [];
        /** @var Movement $movement */
        foreach ($this->getMovementsComming() as $movement){
            if (!isset($return[$movement->getContainer()->getId()])){
                $return[$movement->getContainer()->getId()] = $movement->getQuantity();
            }else{
                $return[$movement->getContainer()->getId()] += $movement->getQuantity();
            }
        }
        foreach ($this->getMovementsLeaving() as $movement){
            if (!isset($return[$movement->getContainer()->getId()])){
                $return[$movement->getContainer()->getId()] = -$movement->getQuantity();
            }else{
                $return[$movement->getContainer()->getId()] -= $movement->getQuantity();
            }
        }
        return $return;
    }

    public function getLabel(): ?string
    {
        if ($this->label)
            return $this->label;
        else
            return $this->__toString();
    }

    public function setLabel(?string $label): self
    {
        $this->label = $label;

        return $this;
    }

}
