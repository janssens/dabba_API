<?php


namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Entities\Traits\AuthCodeTrait;
use League\OAuth2\Server\Entities\ClientEntityInterface;

/**
 * @ORM\Entity
 */
final class AuthCode
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string", length="80")
     */
    protected $identifier;

    /**
     * @ORM\ManyToOne(targetEntity=Client::class)
     */
    protected $client;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $expiry;

    /**
     * @ORM\Column(type="string")
     */
    protected $userIdentifier;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $revoked = false;

    public function __construct(
        string $identifier,
        Client $client,
        \DateTimeImmutable $expiry,
        string $userIdentifier)
    {
        $this->identifier = $identifier;
        $this->client = $client;
        $this->expiry = $expiry;
        $this->userIdentifier = $userIdentifier;
    }

    public function getIdentifier()
    {
        return $this->identifier;
    }

    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
    }

    public function getExpiryDateTime()
    {
        return $this->expiry;
    }

    public function setExpiryDateTime(\DateTimeImmutable $dateTime)
    {
        $this->expiry = $dateTime;
    }

    public function setUserIdentifier($identifier)
    {
        $this->identifier = $identifier;
    }

    public function getUserIdentifier()
    {
        return $this->identifier;
    }

    public function getClient()
    {
        return $this->client;
    }

    public function setClient(ClientEntityInterface $client)
    {
        $this->client = $client;
    }
}
