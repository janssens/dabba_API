<?php

namespace App\Entity;

use App\Repository\CmsRepository;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource(
 *     collectionOperations={
 *         "get",
 *         "post"={"security"="is_granted('ROLE_SUPER_ADMIN')"}
 *     },
 *     itemOperations={
 *          "get"={
 *             "method"="GET",
 *             "controller"=NotFoundAction::class,
 *             "read"=false,
 *             "output"=false,
 *         },
 *     },
 *     normalizationContext={"groups"={"cms:read"}},
 *     denormalizationContext={"groups"={"cms:write"}}
 * )
 * @ORM\Entity(repositoryClass=CmsRepository::class)
 * @Serializer\ExclusionPolicy("ALL")
 */
class Cms
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Serializer\Expose
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Serializer\Expose
     * @Groups({"cms:read"})
     */
    private $title;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"cms:read"})
     */
    private $position;

    /**
     * @ORM\Column(type="json")
     * @Groups({"cms:read"})
     */
    private $css = [];

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"cms:read"})
     */
    private $content;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"cms:read"})
     */
    private $url;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"cms:read"})
     */
    private $button_label;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"cms:read"})
     */
    private $from_date;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"cms:read"})
     */
    private $to_date;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $image;

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

    public function getCss(): array
    {
        return $this->css;
    }

    public function setCss(array $css): self
    {
        $this->css = $css;
        return $this;
    }

    public function addCss(string $key,string $value): self
    {
        $this->css[$key] = $value;
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
}
