<?php

namespace App\Entity;

use App\Repository\CodeRestaurantRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CodeRestaurantRepository::class)
 * @ORM\EntityListeners({"App\EventListener\CodeRestaurantListener"})
 */
class CodeRestaurant
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50, unique=true)
     */
    private $code;

    /**
     * @ORM\Column(type="boolean")
     */
    private $enabled;

    /**
     * @ORM\ManyToOne(targetEntity=Restaurant::class, inversedBy="codes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $restaurant;

    public function __construct()
    {
        $this->setCode(self::makeCode());
    }

    static public function makeCode(int $substring_number = 4){
        $string = str_shuffle(md5(time()));
        $r= '';
        for ($i = 0;$i<$substring_number;$i++){
            $r .= substr($string, 0, 3).'-';
            $string = str_shuffle($string);
        }
        return substr($r,0,strlen($r)-1);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getEnabled(): ?bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

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
}
