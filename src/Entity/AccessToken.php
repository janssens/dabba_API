<?php

namespace App\Entity;

use App\Repository\AccessTokenRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AccessTokenRepository::class)
 */
class AccessToken
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $userId;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $clientId;

    /**
     * @ORM\Column(type="simple_array",nullable=true)
     */
    private $scopes = [];

    /**
     * @ORM\Column(type="boolean")
     */
    private $revoked;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="datetime")
     */
    private $expiresAt;

    /**
     * Token constructor.
     * @param string $id
     * @param string $userId
     * @param string $clientId
     * @param array $scopes
     * @param bool $revoked
     * @param \DateTime $createdAt
     * @param \DateTime $updatedAt
     * @param \DateTimeImmutable $expiresAt
     */
    public function __construct(
        string $id,
        string $userId,
        string $clientId,
        array $scopes,
        bool $revoked,
        \DateTime $createdAt,
        \DateTime $updatedAt,
        \DateTimeImmutable $expiresAt
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->clientId = $clientId;
        $this->scopes = $scopes;
        $this->revoked = $revoked;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->expiresAt = $expiresAt;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): ?string
    {
        return $this->userId;
    }

    public function setUserId(?string $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    public function getClientId(): ?string
    {
        return $this->clientId;
    }

    public function setClientId(?string $clientId): self
    {
        $this->clientId = $clientId;

        return $this;
    }

    public function getScopes(): ?array
    {
        return $this->scopes;
    }

    public function setScopes(array $scopes): self
    {
        $this->scopes = $scopes;

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

    public function isRevoked(){
        return $this->revoked;
    }

    public function revoke(){
        return $this->setRevoked(true);
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
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
