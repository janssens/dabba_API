<?php

namespace App\Entity;

use App\Repository\PaymentTokenRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Dto\PaymentTokenOutput;

/**
 * @ApiResource(
 *     output=PaymentTokenOutput::class,
 *     collectionOperations={},
 *     itemOperations={"get","delete"},
 *     normalizationContext={"groups"={"user:read"}},
 *     denormalizationContext={}
 * )
 * @ORM\Entity(repositoryClass=PaymentTokenRepository::class)
 */
class PaymentToken
{

    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=255)
     */
    private $uuid;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="paymentTokens")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $brand;

    /**
     * @ORM\Column(type="string", length=16)
     */
    private $pan;

    /**
     * @ORM\Column(type="integer")
     */
    private $expiry_month;

    /**
     * @ORM\Column(type="integer")
     */
    private $expiry_year;

    /**
     * @ORM\Column(type="string", length=3, nullable=true)
     */
    private $country;

    public function __construct(string $uuid,User $user)
    {
        $this->setUser($user);
        $this->setUuid($uuid);
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): self
    {
        $this->uuid = $uuid;

        return $this;
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

    public function getBrand(): ?string
    {
        return $this->brand;
    }

    public function setBrand(string $brand): self
    {
        $this->brand = $brand;

        return $this;
    }

    public function getPan(): ?string
    {
        return $this->pan;
    }

    public function setPan(string $pan): self
    {
        $this->pan = $pan;

        return $this;
    }

    public function getExpiryMonth(): ?int
    {
        return $this->expiry_month;
    }

    public function setExpiryMonth(int $expiry_month): self
    {
        $this->expiry_month = $expiry_month;

        return $this;
    }

    public function getExpiryYear(): ?int
    {
        return $this->expiry_year;
    }

    public function setExpiryYear(int $expiry_year): self
    {
        $this->expiry_year = $expiry_year;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): self
    {
        $this->country = $country;

        return $this;
    }
}
