<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Dto\OrderOutput;
/**
 * @ORM\Entity(repositoryClass=OrderRepository::class)
 * @ORM\Table(name="`order`")
 * @ORM\EntityListeners({"App\EventListener\OrderChangedListener"})
 * @ApiResource(
 *     collectionOperations={
 *          "post"={"output"=OrderOutput::class},
 *     },
 *     itemOperations={
 *          "get",
 *          "pay" = {
 *              "method" = "POST",
 *              "route_name" = "api_pay_order",
 *              "openapi_context"={
 *                  "summary"="Pay order with alias",
 *                  "description"="Pay an order using alias",
 *                  "parameters" = {
 *                      {
 *                          "in" = "path",
 *                          "name" = "id",
 *                          "required" = true,
 *                          "schema" = {
 *                              "type" = "integer",
 *                              "minimum" = 1,
 *                          },
 *                          "description" =  "The order Id",
 *                      },
 *                  },
 *                  "requestBody"={
 *                      "required"=true,
 *                      "content"={
 *                          "application/json"={
 *                              "schema"={
 *                                  "type"="object",
 *                                  "properties"={
 *                                      "token_id"={"type"="string","example"="TO_BE_DEFINED_123456789abcdefghi","description" =  "The token uuid"},
 *                                  },
 *                              },
 *                          },
 *                      },
 *                  },
 *              },
 *          },
 *     },
 *     normalizationContext={"groups"={"order:read"}},
 *     denormalizationContext={"groups"={"order:write"}}
 * )
 */
class Order
{
    const STATE_NEW = 0; //
    const STATE_PAID = 1; //PAID 	La transaction a été payée
    const STATE_RUNNING = 2; //RUNNING 	La traitement de la transaction est en cours
    const STATE_UNPAID = 3; //UNPAID 	La transaction n’est pas payée

    const STATUS_NEW = 0; //
    const STATUS_ACCEPTED = 11; 	// PAID Statut d’une transaction de type VERIFICATION dont l’autorisation ou la demande de renseignement a été acceptée.
                            // Ce statut ne peut évoluer. Les transactions dont le statut est “ACCEPTED” ne sont jamais remises en banque.
                            // Une transaction de type VERIFICATION est créée lors de la mise à jour ou la création d’un alias sans paiement.
    const STATUS_AUTHORISED = 12; // PAID Le montant est autorisé et va être capturé automatiquement.
    const STATUS_CAPTURED = 13; // PAID Le montant de la transaction a été autorisé.
    const STATUS_PARTIALLY_AUTHORISED = 14; // PAID La transaction a été partiellement payée.
    const STATUS_AUTHORISED_TO_VALIDATE = 21;  //RUNNING La transaction, créée en validation manuelle, est autorisée.
                                        // Le marchand doit valider manuellement la transaction afin qu’elle soit remise en banque.
                                        // La transaction peut être validée tant que la date d’expiration de la demande d’autorisation n’est pas dépassée.
                                        // Si cette date est dépassée alors le paiement prend le statut EXPIRED. Le statut Expiré est définitif.
    const STATUS_WAITING_AUTHORISATION = 22; //RUNNING La transaction n’a pas encore été autorisée car le délai de remise est supérieur à la durée de validité de l’autorisation.
                                        // La demande d’autorisation sera déclenchée automatiquement à J-1 avant la date de remise en banque.
                                        // La remise en banque sera automatique.
    const STATUS_WAITING_AUTHORISATION_TO_VALIDATE = 23; //RUNNING Le moyen de paiement a été vérifié mais la transaction n’a pas encore été autorisée
                                        // car le délai de remise est supérieur à la durée de validité de l’autorisation.
                                        // La demande d’autorisation sera déclenchée automatiquement à J-1 avant la date de remise en banque et une intervention manuelle
                                        // sera nécessaire pour confirmer l’autorisation. Rien ne garantit que la demande d’autorisation sera acceptée.
    const STATUS_REFUSED = 31; //UNPAID La transaction a été refusée.
    const STATUS_ERROR = 32; //UNPAID Une erreur non prévue a eu lieu.
    const STATUS_CANCELLED = 33; // UNPAID La transaction a été annulée.
    const STATUS_EXPIRED = 34; // UNPAID La transaction est expirée (le marchand ne l’a pas validé dans le délai imparti).

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"order:read"})
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"order:read","order:write"})
     */
    private $amount;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"user:read"})
     */
    private $status;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"user:read"})
     */
    private $state;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="orders")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"order:read"})
     */
    private $user;

    /**
     * @ORM\Column(type="datetime")
     */
    private $created_at;

    /**
     * @ORM\OneToMany(targetEntity=Transaction::class, mappedBy="parent", orphanRemoval=true)
     * @ORM\OrderBy({"creationDate" = "ASC"})
     */
    private $transactions;

    public function __construct(){
        $this->created_at = new \DateTime('now');
        $this->transactions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @Groups({"order:read"})
     */
    public function getCurrency(): ?string
    {
        return 'EUR';
    }

    public function getAmount(): ?int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getStatusAsString(): ?string
    {
        return self::statusToString($this->status);
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

    /**
     * @Groups({"order:read"})
     */
    public function getCurrentState() :string
    {
        return self::stateToString($this->getState());
    }

    public function getState(): ?int
    {
        return $this->state;
    }

    public function getStateAsString(): ?string
    {
        return self::stateToString($this->state);
    }

    public function setState(int $state): self
    {
        $this->state = $state;

        return $this;
    }

    static function stateFromString(string $string) : int
    {
        $code = self::STATE_NEW;
        switch ($string){
            case 'PAID':
                $code = self::STATE_PAID;
                break;
            case 'RUNNING':
                $code = self::STATE_RUNNING;
                break;
            case 'UNPAID':
                $code = self::STATE_UNPAID;
                break;
            default:
                break;
        }
        return $code;
    }

    static function stateToString(int $state) : string
    {
        switch ($state) {
            case self::STATE_NEW:
                return 'NEW';
                break;
            case self::STATE_PAID:
                return 'PAID';
                break;
            case self::STATE_RUNNING:
                return 'RUNNING';
                break;
            case self::STATE_UNPAID:
                return 'UNPAID';
                break;
            default:
                return 'N/A';
        }
    }

    static function statusFromString(string $string) : int
    {
        $code = self::STATUS_NEW;
        switch ($string){
            case 'ACCEPTED':
                $code = self::STATUS_ACCEPTED;
                break;
            case 'AUTHORISED':
                $code = self::STATUS_AUTHORISED;
                break;
            case 'CAPTURED':
                $code = self::STATUS_CAPTURED;
                break;
            case 'PARTIALLY_AUTHORISED':
                $code = self::STATUS_PARTIALLY_AUTHORISED;
                break;
            case 'WAITING_AUTHORISATION':
                $code = self::STATUS_WAITING_AUTHORISATION;
                break;
            case 'AUTHORISED_TO_VALIDATE':
                $code = self::STATUS_AUTHORISED_TO_VALIDATE;
                break;
            case 'WAITING_AUTHORISATION_TO_VALIDATE':
                $code = self::STATUS_WAITING_AUTHORISATION_TO_VALIDATE;
                break;
            case 'REFUSED':
                $code = self::STATUS_REFUSED;
                break;
            case 'ERROR':
                $code = self::STATUS_ERROR;
                break;
            case 'CANCELLED':
                $code = self::STATUS_CANCELLED;
                break;
            case 'EXPIRED':
                $code = self::STATUS_EXPIRED;
                break;
            default:
                break;
        }
        return $code;
    }

    static function statusToString(int $satus) : string
    {
        switch ($satus) {
            case self::STATUS_ACCEPTED:
                return 'ACCEPTED';
                break;
            case self::STATUS_AUTHORISED;
                return 'AUTHORISED';
                break;
            case self::STATUS_CAPTURED;
                return 'CAPTURED';
                break;
            case self::STATUS_PARTIALLY_AUTHORISED;
                return 'PARTIALLY_AUTHORISED';
                break;
            case self::STATUS_WAITING_AUTHORISATION;
                return 'WAITING_AUTHORISATION';
                break;
            case self::STATUS_AUTHORISED_TO_VALIDATE;
                return 'AUTHORISED_TO_VALIDATE';
                break;
            case self::STATUS_WAITING_AUTHORISATION_TO_VALIDATE;
                return 'WAITING_AUTHORISATION_TO_VALIDATE';
                break;
            case self::STATUS_REFUSED;
                return 'REFUSED';
                break;
            case self::STATUS_ERROR;
                return 'ERROR';
                break;
            case self::STATUS_CANCELLED;
                return 'CANCELLED';
                break;
            case self::STATUS_EXPIRED;
                return 'EXPIRED';
                break;
            default:
                return 'N/A';
        }
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    /**
     * @return Collection|Transaction[]
     */
    public function getTransactions(): Collection
    {
        return $this->transactions;
    }

    public function getTransactionsAsTxt(): string
    {
        $transations = [];
        foreach ($this->getTransactions() as $transaction){
            $transations[] = $transaction->__toString();
        }
        return implode(",\n",$transations);
    }

    public function addTransaction(Transaction $transaction): self
    {
        if (!$this->transactions->contains($transaction)) {
            $this->transactions[] = $transaction;
            $transaction->setParent($this);
        }

        return $this;
    }

    public function removeTransaction(Transaction $transaction): self
    {
        if ($this->transactions->removeElement($transaction)) {
            // set the owning side to null (unless already changed)
            if ($transaction->getParent() === $this) {
                $transaction->setParent(null);
            }
        }

        return $this;
    }

    public function getSystemPayId(): ?string
    {
        return $this->created_at->format('ymdHis').str_pad($this->id, 10, "0", STR_PAD_LEFT);
    }

}
