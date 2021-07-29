<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Dto\OrderOutput;
/**
 * @ORM\Entity(repositoryClass=OrderRepository::class)
 * @ORM\Table(name="`order`")
 * @ApiResource(
 *     collectionOperations={
 *          "post"={"output"=OrderOutput::class},
 *     },
 *     itemOperations={"get"},
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

    public function __construct(){
        $this->created_at = new \DateTime('now');
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
        switch ($this->state) {
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

    public function getState(): ?int
    {
        return $this->state;
    }

    public function setState(int $state): self
    {
        $this->state = $state;

        return $this;
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
}
