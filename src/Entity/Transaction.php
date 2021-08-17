<?php

namespace App\Entity;

use App\Repository\TransactionRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TransactionRepository::class)
 * @ORM\EntityListeners({"App\EventListener\TransactionListener"})
 */
class Transaction
{
    const PAYMENT_METHOD_DEFAULT = 0;
    const PAYMENT_METHOD_CARD = 1;

    const OPERATION_TYPE_DEFAULT = 0;
    const OPERATION_TYPE_CREDIT = 1;
    const OPERATION_TYPE_DEBIT = 2;

    const MODE_NA = 0;
    const MODE_PROD = 1;
    const MODE_TEST = 2;

    /**
     * @ORM\Id
     * @ORM\Column(type="string")
     */
    private $uuid;

    /**
     * @ORM\Column(type="integer")
     */
    private $shopId;

    /**
     * @ORM\Column(type="float")
     */
    private $amount;

    /**
     * @ORM\Column(type="string", length=3)
     */
    private $currency;

    /**
     * @ORM\Column(type="smallint")
     */
    private $paymentMethodType;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $paymentMethodToken;

    /**
     * @ORM\Column(type="smallint")
     */
    private $status;

    /**
     * @ORM\Column(type="smallint")
     */
    private $detailedStatus;

    /**
     * @ORM\Column(type="smallint")
     */
    private $operationType;

    /**
     * @ORM\Column(type="boolean")
     */
    private $effectiveStrongAuthentication;

    /**
     * @ORM\Column(type="datetime")
     */
    private $creationDate;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $errorCode;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $errorMessage;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $detailedErrorCode;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $detailedErrorMessage;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $metadata = [];

    /**
     * @ORM\ManyToOne(targetEntity=Order::class, inversedBy="transactions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $parent;

    /**
     * @ORM\Column(type="smallint")
     */
    private $mode;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUuid()
    {
        return $this->uuid;
    }

    public function setUuid($uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getShopId(): ?int
    {
        return $this->shopId;
    }

    public function setShopId(int $shopId): self
    {
        $this->shopId = $shopId;

        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): self
    {
        $this->currency = $currency;

        return $this;
    }

    public function getPaymentMethodType(): ?int
    {
        return $this->paymentMethodType;
    }

    public function setPaymentMethodType(int $paymentMethodType): self
    {
        $this->paymentMethodType = $paymentMethodType;

        return $this;
    }

    public function setPaymentMethodTypeFromString(string $paymentMethodType): self
    {
        $code = self::PAYMENT_METHOD_DEFAULT;
        switch ($paymentMethodType){
            case 'CARD':
                $code = self::PAYMENT_METHOD_CARD;
                break;
            default:
                break;
        }
        $this->paymentMethodType = $code;

        return $this;
    }

    public function getPaymentMethodToken(): ?string
    {
        return $this->paymentMethodToken;
    }

    public function setPaymentMethodToken(?string $paymentMethodToken): self
    {
        $this->paymentMethodToken = $paymentMethodToken;

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

    public function setStatusFromString(string $status): self
    {
        $this->status = Order::stateFromString($status);
        return $this;
    }

    public function getDetailedStatus(): ?int
    {
        return $this->detailedStatus;
    }

    public function setDetailedStatus(int $detailedStatus): self
    {
        $this->detailedStatus = $detailedStatus;

        return $this;
    }

    public function setDetailedStatusFromString(string $detailedStatus): self
    {
        $this->detailedStatus = Order::statusFromString($detailedStatus);
        return $this;
    }

    public function getOperationType(): ?int
    {
        return $this->operationType;
    }

    public function setOperationType(int $operationType): self
    {
        $this->operationType = $operationType;

        return $this;
    }

    public function setOperationTypeFromString(string $operationType): self
    {
        $code = self::OPERATION_TYPE_DEFAULT;
        switch ($operationType){
            case 'DEBIT':
                $code = self::OPERATION_TYPE_DEBIT;
                break;
            case 'CREDIT':
                $code = self::OPERATION_TYPE_CREDIT;
                break;
            default:
                break;
        }

        $this->operationType = $code;

        return $this;
    }

    public function getEffectiveStrongAuthentication(): ?bool
    {
        return $this->effectiveStrongAuthentication;
    }

    public function setEffectiveStrongAuthentication(bool $effectiveStrongAuthentication): self
    {
        $this->effectiveStrongAuthentication = $effectiveStrongAuthentication;

        return $this;
    }

    public function getCreationDate(): ?\DateTimeInterface
    {
        return $this->creationDate;
    }

    public function setCreationDate(\DateTimeInterface $creationDate): self
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    public function getErrorCode(): ?int
    {
        return $this->errorCode;
    }

    public function setErrorCode(int $errorCode): self
    {
        $this->errorCode = $errorCode;

        return $this;
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function setErrorMessage(?string $errorMessage): self
    {
        $this->errorMessage = $errorMessage;

        return $this;
    }

    public function getDetailedErrorCode(): ?string
    {
        return $this->detailedErrorCode;
    }

    public function setDetailedErrorCode(?string $detailedErrorCode): self
    {
        $this->detailedErrorCode = $detailedErrorCode;

        return $this;
    }

    public function getDetailedErrorMessage(): ?string
    {
        return $this->detailedErrorMessage;
    }

    public function setDetailedErrorMessage(?string $detailedErrorMessage): self
    {
        $this->detailedErrorMessage = $detailedErrorMessage;

        return $this;
    }

    public function getMetadata(): ?array
    {
        return $this->metadata;
    }

    public function setMetadata(?array $metadata): self
    {
        $this->metadata = $metadata;

        return $this;
    }

    public function getParent(): ?Order
    {
        return $this->parent;
    }

    public function setParent(?Order $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    public function getMode(): ?int
    {
        return $this->mode;
    }

    public function setMode(int $mode): self
    {
        $this->mode = $mode;

        return $this;
    }

    public function setModeFromString(string $mode): self
    {
        $code = self::MODE_NA;
        switch ($mode){
            case 'TEST':
                $code = self::MODE_TEST;
                break;
            case 'PROD':
                $code = self::MODE_PROD;
                break;
            default:
                break;
        }

        $this->mode = $code;

        return $this;
    }
}
