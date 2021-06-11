<?php

namespace App\Entity;

use App\Repository\RefreshTokenRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=RefreshTokenRepository::class)
 */
class RefreshToken
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $accessTokenId;

    /**
     * @ORM\Column(type="boolean")
     */
    private $revoked = false;

    /**
     * @ORM\Column(type="datetime")
     */
    private $expiresAt;

    /**
     * RefreshToken constructor.
     * @param string $id
     * @param string $accessTokenId
     * @param \DateTimeImmutable $expiresAt
     */
    public function __construct(string $id, string $accessTokenId, \DateTimeImmutable $expiresAt)
    {
        $this->id = $id;
        $this->accessTokenId = $accessTokenId;
        $this->expiresAt = $expiresAt;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAccessTokenId(): ?string
    {
        return $this->accessTokenId;
    }

    public function setAccessTokenId(string $accessTokenId): self
    {
        $this->accessTokenId = $accessTokenId;

        return $this;
    }

    public function getRevoked(): ?bool
    {
        return $this->revoked;
    }

    public function setRevoked(bool $revoked): self
    {
        $this->revoked = $revoked;

        return $this;
    }

    public function revoke(){
        return $this->setRevoked(true);
    }

    public function getExpiresAt(): ?\DateTimeInterface
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(\DateTimeInterface $expiresAt): self
    {
        $this->expiresAt = $expiresAt;

        return $this;
    }
}
