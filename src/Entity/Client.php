<?php

namespace App\Entity;

use App\Repository\ClientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

/**
 * @ORM\Entity(repositoryClass=ClientRepository::class)
 */
class Client
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $secret;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $redirect;

    /**
     * @ORM\Column(type="boolean")
     */
    private $active;

    /**
     * @ORM\OneToMany(targetEntity=AccessToken::class, mappedBy="client")
     */
    private $accessTokens;

    /**
     * Client constructor.
     * @param ClientId $clientId
     * @param string $name
     */
    private function __construct(ClientId $clientId, string $name)
    {
        $this->id = $clientId->toString();
        $this->name = $name;
        $this->secret = Uuid::uuid1()->toString();
    }

    public static function create(string $name): Client
    {
        $clientId = ClientId::fromString(Uuid::uuid4()->toString());
        return new self($clientId, $name);
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getClientId(): ClientId
    {
        return ClientId::fromString($this->id);
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

    public function getSecret(): ?string
    {
        return $this->secret;
    }

    public function setSecret(string $secret): self
    {
        $this->secret = $secret;

        return $this;
    }

    public function getRedirect(): ?string
    {
        return $this->redirect;
    }

    public function setRedirect(string $redirect): self
    {
        $this->redirect = $redirect;

        return $this;
    }

    public function getActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    /**
     * @return Collection|AccessToken[]
     */
    public function getAccessTokens(): Collection
    {
        return $this->accessTokens;
    }

    public function addAccessToken(AccessToken $accessToken): self
    {
        if (!$this->accessTokens->contains($accessToken)) {
            $this->accessTokens[] = $accessToken;
            $accessToken->setClient($this);
        }

        return $this;
    }

    public function removeAccessToken(AccessToken $accessToken): self
    {
        if ($this->accessTokens->removeElement($accessToken)) {
            // set the owning side to null (unless already changed)
            if ($accessToken->getClient() === $this) {
                $accessToken->setClient(null);
            }
        }

        return $this;
    }
}
