<?php

namespace App\Entity;

use App\Repository\CmsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Core\Action\NotFoundAction;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\NumericFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use Doctrine\ORM\Mapping\JoinColumn;

/**
 * @ApiResource(
 *     collectionOperations={
 *         "get",
 *         "stat" = {
 *              "method" = "GET",
 *              "route_name" = "api_global_stats",
 *              "openapi_context"={
 *                  "summary"="Get global app data",
 *                  "description"="Get global app data",
 *                  "responses" = {
 *                      "200" = {
 *                          "description" = "Global app data",
 *                          "content" =  {
 *                              "application/json"={
 *                                  "schema"={
 *                                      "type"="object",
 *                                      "properties"={
 *                                          "avoidedWaste"={"type"="integer","example"="431762"},
 *                                          "massOfAvoidedWaste"={"type"="number","example"="4000.0"},
 *                                      },
 *                                  },
 *                              },
 *                          }
 *                      }
 *                  }
 *              },
 *              "filters" = {}
 *          }
 *     },
 *     itemOperations={
 *          "get"
 *     },
 *     normalizationContext={"groups"={"cms:read"}},
 *     denormalizationContext={"groups"={"cms:write"}},
 *     attributes={"order"={"position": "ASC"}}
 * )
 * @ApiFilter(NumericFilter::class, properties={"zone.id"})
 * @ApiFilter(SearchFilter::class, properties={"category":"exact"})
 * @ORM\Entity(repositoryClass=CmsRepository::class)
 * @Serializer\ExclusionPolicy("ALL")
 */
class Cms
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"cms:read","zone:read"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50)
     * @Groups({"cms:read","zone:read"})
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"cms:read","zone:read"})
     */
    private $subtitle;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"cms:read","zone:read"})
     */
    private $position;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"cms:read","zone:read"})
     */
    private $content;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"cms:read","zone:read"})
     */
    private $url;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"cms:read","zone:read"})
     */
    private $button_label;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"cms:read","zone:read"})
     */
    private $from_date;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"cms:read","zone:read"})
     */
    private $to_date;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"cms:read","zone:read"})
     */
    private $image;

    /**
     * @ORM\Column(type="smallint")
     * @Groups({"cms:read","zone:read"})
     */
    private $format;

    /**
     * @ORM\ManyToOne(targetEntity=Color::class)
     * @JoinColumn(onDelete="CASCADE")
     * @Groups({"cms:read","zone:read"})
     */
    private $textColor;

    /**
     * @ORM\ManyToOne(targetEntity=Color::class)
     * @JoinColumn(onDelete="CASCADE")
     * @Groups({"cms:read","zone:read"})
     */
    private $backgroundColor;

    /**
     * @ORM\ManyToMany(targetEntity=Zone::class, inversedBy="cms")
     */
    private $zone;

    /**
     * @ORM\Column(type="boolean")
     */
    private $is_public;

    /**
     * @ORM\Column(type="string", length=50)
     * @Groups({"cms:read","zone:read"})
     */
    private $category;

    public function __construct()
    {
        $this->zone = new ArrayCollection();
    }

    const FORMAT_SMALL = 1;
    const FORMAT_FULL = 2;

    const CATEGORY_HOME = 'HOME';
    const CATEGORY_MY_DABBA = 'MY_DABBA';

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getButtonLabel(): ?string
    {
        return $this->button_label;
    }

    public function setButtonLabel(?string $button_label): self
    {
        $this->button_label = $button_label;

        return $this;
    }

    public function getFromDate(): ?\DateTimeInterface
    {
        return $this->from_date;
    }

    public function setFromDate(?\DateTimeInterface $from_date): self
    {
        $this->from_date = $from_date;

        return $this;
    }

    public function getToDate(): ?\DateTimeInterface
    {
        return $this->to_date;
    }

    public function setToDate(?\DateTimeInterface $to_date): self
    {
        $this->to_date = $to_date;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getFormat(): ?int
    {
        return $this->format;
    }

    public function setFormat(int $format): self
    {
        $this->format = $format;

        return $this;
    }

    public function getTextColor(): ?Color
    {
        return $this->textColor;
    }

    public function setTextColor(?Color $textColor): self
    {
        $this->textColor = $textColor;

        return $this;
    }

    public function getBackgroundColor(): ?Color
    {
        return $this->backgroundColor;
    }

    public function setBackgroundColor(?Color $backgroundColor): self
    {
        $this->backgroundColor = $backgroundColor;

        return $this;
    }

    public function getSubtitle(): ?string
    {
        return $this->subtitle;
    }

    public function setSubtitle(?string $subtitle): self
    {
        $this->subtitle = $subtitle;

        return $this;
    }

    /**
     * @return Collection|Zone[]
     */
    public function getZone(): Collection
    {
        return $this->zone;
    }

    public function addZone(Zone $zone): self
    {
        if (!$this->zone->contains($zone)) {
            $this->zone[] = $zone;
        }

        return $this;
    }

    public function removeZone(Zone $zone): self
    {
        $this->zone->removeElement($zone);

        return $this;
    }

    public function getIsPublic(): ?bool
    {
        return $this->is_public;
    }

    public function setIsPublic(bool $is_public): self
    {
        $this->is_public = $is_public;

        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(string $category): self
    {
        $this->category = $category;

        return $this;
    }
}
