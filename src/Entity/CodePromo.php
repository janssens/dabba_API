<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\CodePromoRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CodePromoRepository::class)
 * @ORM\EntityListeners({"App\EventListener\CodePromoListener"})
 * @ApiResource(
 *     collectionOperations={
 *          "promocode_apply" = {
 *              "method" = "POST",
 *              "route_name" = "api_apply_code_promo",
 *              "openapi_context"={
 *                  "summary"="Apply promo code",
 *                  "description"="Apply a promo using code",
 *                  "requestBody"={
 *                      "required"=true,
 *                      "content"={
 *                          "application/json"={
 *                              "schema"={
 *                                  "type"="object",
 *                                  "properties"={
 *                                      "code"={"type"="string","example"="123-abc-def","description" =  "The promo code"},
 *                                  },
 *                              },
 *                          },
 *                      },
 *                  },
 *                  "responses" = {
 *                      "201" = {
 *                          "description" = "apply success",
 *                          "content" =  {
 *                              "application/json"={
 *                                  "schema"={
 *                                      "type"="object",
 *                                      "properties"={
 *                                          "success"={"type"="boolean","example"="true"},
 *                                          "new_wallet_amount"={"type"="number","example"="4.0"},
 *                                      },
 *                                  },
 *                              },
 *                          }
 *                      }
 *                  }
 *              },
 *              "filters" = {}
 *          },
 *     },
 *     itemOperations={},
 *     normalizationContext={"groups"={"order:read"}},
 *     denormalizationContext={"groups"={"order:write"}}
 * )
 */
class CodePromo
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $code;

    /**
     * @ORM\Column(type="boolean")
     */
    private $enabled;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $used_at;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="codePromos")
     */
    private $used_by;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $expired_at;

    /**
     * @ORM\Column(type="integer")
     */
    private $amount;

    public function __construct()
    {
        $this->setCode(self::makeCode());
    }

    static public function makeCode(int $substring_number = 3){
        $string = str_shuffle(md5(time()));
        $r= '';
        for ($i = 0;$i<$substring_number;$i++){
            $r .= substr($string, 0, 3).'-';
            $string = str_shuffle($string);
        }
        return substr($r,0,strlen($r)-1);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getEnabled(): ?bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function getUsedAt(): ?\DateTimeInterface
    {
        return $this->used_at;
    }

    public function setUsedAt(?\DateTimeInterface $used_at): self
    {
        $this->used_at = $used_at;

        return $this;
    }

    public function getUsedBy(): ?User
    {
        return $this->used_by;
    }

    public function setUsedBy(?User $used_by): self
    {
        $this->used_by = $used_by;

        return $this;
    }

    public function getExpiredAt(): ?\DateTimeInterface
    {
        return $this->expired_at;
    }

    public function setExpiredAt(?\DateTimeInterface $expired_at): self
    {
        $this->expired_at = $expired_at;

        return $this;
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
}
