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
}
