<?php

namespace App\Entity;

use App\Repository\CmsRepository;
use Doctrine\ORM\Mapping as ORM;
use OpenApi\Annotations as OA;
use JMS\Serializer\Annotation as Serializer;

/**
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
     * @OA\Property(description="The unique identifier of the cms block")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Serializer\Expose
     * @OA\Property(description="Title in the block")
     */
    private $title;

    /**
     * @ORM\Column(type="integer")
     * @Serializer\Expose
     * @OA\Property(description="Sort order of the block")
     */
    private $position;

    /**
     * @ORM\Column(type="json")
     * @Serializer\Expose
     * @OA\Property(description="Css properties")
     */
    private $css = [];

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Serializer\Expose
     * @OA\Property(description="Text content")
     */
    private $content;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Serializer\Expose
     * @OA\Property(description="Url for link",nullable=true)
     */
    private $url;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Serializer\Expose
     * @OA\Property(description="Label for button")
     */
    private $button_label;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $from_date;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $to_date;

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
}
