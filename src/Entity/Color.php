<?php

namespace App\Entity;

use App\Repository\ColorRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Core\Annotation\ApiResource;

/**
 * @ORM\Entity(repositoryClass=ColorRepository::class)
 * @ApiResource(
 *     collectionOperations={"get"},
 *     itemOperations={"get"},
 *     normalizationContext={"groups"={"color:read"}},
 *     denormalizationContext={"groups"={"color:write"}}
 * )
 */
class Color
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"color:read","cms:read","zone:read"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=25)
     * @Groups({"color:read","cms:read","zone:read"})
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=7)
     * @Groups({"color:read","cms:read","zone:read"})
     */
    private $code;

    public function __toString(){
        return $this->getName().':'.$this->getCode();
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

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }
}
