<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use JMS\Serializer\Annotation as Serializer;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Core\Annotation\ApiSubresource;
use Symfony\Component\Serializer\Annotation\SerializedName;
use ApiPlatform\Core\Action\NotFoundAction;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @UniqueEntity(fields={"email"}, message="There is already an account with this email")
 * @ApiResource(
 *     collectionOperations={
 *          "post",
 *          "reset_password" = {
 *              "method" = "POST",
 *              "route_name" = "api_forgot_password_request",
 *              "openapi_context"={
 *                  "summary"="Request a reset password link",
 *                  "description"="Request a reset password link",
 *                  "requestBody"={
 *                      "required"=true,
 *                      "content"={
 *                          "application/x-www-form-urlencoded"={
 *                              "schema"={
 *                                  "type"="object",
 *                                  "properties"={
 *                                      "username"={"type"="string"},
 *                                  },
 *                              },
 *                          },
 *                      },
 *                  },
 *              },
 *          },
 *          "current_user" = {
 *              "method" = "GET",
 *              "route_name" = "api_current_user",
 *              "openapi_context"={
 *                  "summary"="Get current user data",
 *                  "description"="Get current user data",
 *              },
 *          }
 *     },
 *     itemOperations={
 *         "get"={
 *             "method"="GET",
 *             "controller"=NotFoundAction::class,
 *             "read"=false,
 *             "output"=false,
 *         },
 *         "put"={
 *              "security"="is_granted('edit', object)"
 *         },
 *         "add_to_favorite" = {
 *              "method" = "GET",
 *              "route_name" = "api_add_to_favorite",
 *              "openapi_context"={
 *                  "summary"="Add restaurant to favorite",
 *                  "description"="Add restaurant to favorite",
 *                  "parameters" = {
 *                      {
 *                          "in" = "path",
 *                          "name" = "id",
 *                          "required" = true,
 *                          "schema" = {
 *                              "type" = "integer",
 *                              "minimum" = 1,
 *                          },
 *                          "description" =  "The restaurant Id",
 *                      }
 *                  }
 *              },
 *          },
 *         "remove_from_favorite" = {
 *              "method" = "GET",
 *              "route_name" = "api_remove_from_favorite",
 *              "openapi_context"={
 *                  "summary"="Remove restaurant from favorite",
 *                  "description"="Remove restaurant from favorite",
 *                  "parameters" = {
 *                      {
 *                          "in" = "path",
 *                          "name" = "id",
 *                          "required" = true,
 *                          "schema" = {
 *                              "type" = "integer",
 *                              "minimum" = 1,
 *                          },
 *                          "description" =  "The restaurant Id",
 *                      }
 *                  }
 *              },
 *          },
 *     },
 *     normalizationContext={"groups"={"user:read"}},
 *     denormalizationContext={"groups"={"user:write"}},
 *     attributes={"pagination_enabled"=false}
 * )
 */
class User implements UserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Serializer\Expose
     * @Groups({"user:read","order:read","trade:read"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"user:read","user:write","order:read","trade:read"})
     */
    private $email;

    /**
     * @ORM\ManyToOne(targetEntity=Zone::class, inversedBy="admins")
     */
    private $owned_zone;

    /**
     * @ORM\OneToMany(targetEntity=Order::class, mappedBy="user", orphanRemoval=true)
     * @ApiSubresource
     */
    private $orders;

    /**
     * @ORM\ManyToOne(targetEntity=Zone::class, inversedBy="users")
     * @Groups({"user:read"})
     */
    private $zone;

    /**
     * @Groups("user:write")
     * @SerializedName("password")
     */
    private $plainPassword;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"user:read","user:write"})
     */
    private $firstname;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"user:read","user:write"})
     */
    private $lastname;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @Groups({"user:write"})
     */
    private $dob;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isVerified = false;

    /**
     * @ORM\OneToMany(targetEntity=AccessToken::class, mappedBy="user", orphanRemoval=true)
     */
    private $accessTokens;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @ORM\ManyToMany(targetEntity=Restaurant::class, mappedBy="fans")
     * @Groups({"user:read","user:write"})
     * @ApiSubresource
     */
    private $restaurants;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"user:read"})
     */
    private $wallet;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"user:read"})
     */
    private $fidelity;

    /**
     * @ORM\OneToMany(targetEntity=Trade::class, mappedBy="user", orphanRemoval=true)
     */
    private $trades;

    /**
     * @ORM\OneToOne(targetEntity=Stock::class, mappedBy="user", cascade={"persist", "remove"})
     */
    private $stock;

    /**
     * @ORM\OneToMany(targetEntity=PaymentToken::class, mappedBy="user", orphanRemoval=true)
     * @Groups({"user:read"})
     */
    private $paymentTokens;

    public function __construct()
    {
        $this->orders = new ArrayCollection();
        $this->accessTokens = new ArrayCollection();
        $this->restaurants = new ArrayCollection();
        $this->trades = new ArrayCollection();
        $this->paymentTokens = new ArrayCollection();
    }

    public function __toString(): ?string
    {
        return $this->firstname.' '.$this->lastname.' ('.$this->getEmail().')';
    }

    /**
     * @Groups({"user:read"})
     */
    public function getAvoidedWaste():?int
    {
        $counter = 0;
        /** @var Trade $trade */
        foreach ($this->getTrades() as $trade){
            /** @var TradeItem $item */
            foreach ($trade->getItems() as $item){
                if ($item->getType()==TradeItem::TYPE_WITHDRAW){
                    $counter += $item->getQuantity();
                }
            }
        }
        return $counter;
    }

    /**
     * @Groups({"user:read"})
     */
    public function getMassOfAvoidedWaste(): ?float
    {
        $counter = 0;
        /** @var Trade $trade */
        foreach ($this->getTrades() as $trade){
            /** @var TradeItem $item */
            foreach ($trade->getItems() as $item){
                if ($item->getType()==TradeItem::TYPE_WITHDRAW){
                    $counter += $item->getQuantity()*$item->getContainer()->getWeightOfSavedWaste();
                }
            }
        }
        return $counter;
    }

    /**
     * @Groups({"user:read"})
     */
    public function getContainers(): ?array
    {
        if (!$this->getStock()){
            return [];
        }
        $containers = $this->getStock()->getContainers();
        foreach ($containers as $id => $container_qty){
            if ($container_qty<=0){
                unset($containers[$id]);
            }
        }
        return $containers;
    }

    public function canGive(array $request): ?bool
    {
        $containers = $this->getContainers();
        foreach ($request as $container_id => $qty){
            if (!isset($containers[$container_id])||$containers[$container_id]<-$qty){
                return false;
            }
        }
        return true;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getOwnedZone(): ?Zone
    {
        return $this->owned_zone;
    }

    public function setOwnedZone(?Zone $zone): self
    {
        $this->owned_zone = $zone;

        return $this;
    }

    /**
     * @return Collection|Order[]
     */
    public function getOrders(): Collection
    {
        return $this->orders;
    }

    public function addOrder(Order $order): self
    {
        if (!$this->orders->contains($order)) {
            $this->orders[] = $order;
            $order->setUser($this);
        }

        return $this;
    }

    public function removeOrder(Order $order): self
    {
        if ($this->orders->removeElement($order)) {
            // set the owning side to null (unless already changed)
            if ($order->getUser() === $this) {
                $order->setUser(null);
            }
        }

        return $this;
    }

    public function getUsername()
    {
        return $this->getEmail();
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

    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        $this->plainPassword = null;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(string $password): ? self
    {
        $this->plainPassword = $password;

        return $this;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function addRole(string $role): self
    {
        $roles = $this->roles;
        $roles[] = $role;
        $this->roles = array_unique($roles);
        return $this;
    }

    public function hasRoles(string $roles): bool
    {
        return in_array($roles, $this->roles);
    }

    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @param ArrayCollection $orders
     */
    public function setOrders(ArrayCollection $orders): void
    {
        $this->orders = $orders;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(?string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(?string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getFullname(): string
    {
        return $this->getFirstname() . ' ' . $this->getLastname();
    }

    public function getDob(): ?\DateTimeInterface
    {
        return $this->dob;
    }

    public function setDob(?\DateTimeInterface $dob): self
    {
        $this->dob = $dob;

        return $this;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;

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
            $accessToken->setUser($this);
        }

        return $this;
    }

    public function removeAccessToken(AccessToken $accessToken): self
    {
        if ($this->accessTokens->removeElement($accessToken)) {
            // set the owning side to null (unless already changed)
            if ($accessToken->getUser() === $this) {
                $accessToken->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Restaurant[]
     */
    public function getRestaurants(): Collection
    {
        return $this->restaurants;
    }

    public function addRestaurant(Restaurant $restaurant): self
    {
        if (!$this->restaurants->contains($restaurant)) {
            $this->restaurants[] = $restaurant;
            $restaurant->addFan($this);
        }

        return $this;
    }

    public function removeRestaurant(Restaurant $restaurant): self
    {
        if ($this->restaurants->removeElement($restaurant)) {
            $restaurant->removeFan($this);
        }

        return $this;
    }

    public function getWallet(): ?float
    {
        if (!$this->wallet)
            return 0;
        return $this->wallet;
    }

    public function setWallet(?float $wallet): self
    {
        $this->wallet = $wallet;

        return $this;
    }

    public function getFidelity(): ?int
    {
        if (!$this->fidelity)
            return 0;
        return $this->fidelity;
    }

    public function setFidelity(?int $fidelity): self
    {
        $this->fidelity = $fidelity;

        return $this;
    }

    /**
     * @return Collection|Trade[]
     */
    public function getTrades(): Collection
    {
        return $this->trades;
    }

    public function getStock(): ?Stock
    {
        return $this->stock;
    }

    public function setStock(?Stock $stock): self
    {
        // unset the owning side of the relation if necessary
        if ($stock === null && $this->stock !== null) {
            $this->stock->setUser(null);
        }

        // set the owning side of the relation if necessary
        if ($stock !== null && $stock->getUser() !== $this) {
            $stock->setUser($this);
        }

        $this->stock = $stock;

        return $this;
    }

    /**
     * @return Collection|PaymentToken[]
     */
    public function getPaymentTokens(): Collection
    {
        return $this->paymentTokens;
    }

    public function addPaymentToken(PaymentToken $paymentToken): self
    {
        if (!$this->paymentTokens->contains($paymentToken)) {
            $this->paymentTokens[] = $paymentToken;
            $paymentToken->setUser($this);
        }

        return $this;
    }

    public function removePaymentToken(PaymentToken $paymentToken): self
    {
        if ($this->paymentTokens->removeElement($paymentToken)) {
            // set the owning side to null (unless already changed)
            if ($paymentToken->getUser() === $this) {
                $paymentToken->setUser(null);
            }
        }

        return $this;
    }
}
