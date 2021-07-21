<?php

namespace App\Entity;

use App\Repository\ZoneRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiFilter;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\RangeFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\NumericFilter;

/**
 * @ORM\Entity(repositoryClass=ZoneRepository::class)
 * @ApiResource(
 *     collectionOperations={
 *         "get"
 *     },
 *     itemOperations={"get"},
 *     normalizationContext={"groups"={"zone:read"}},
 *     denormalizationContext={"groups"={"zone:write"}}
 * )
 */
class Zone
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"zone:read"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"zone:read"})
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity=Restaurant::class, mappedBy="zone", orphanRemoval=true)
     */
    private $restaurants;

    /**
     * @ORM\OneToMany(targetEntity=User::class, mappedBy="owned_zone")
     */
    private $admins;

    /**
     * @ORM\OneToMany(targetEntity=User::class, mappedBy="zone")
     */
    private $users;

    /**
     * @ORM\ManyToMany(targetEntity=Cms::class, mappedBy="zone")
     * @Groups({"zone:read"})
     */
    private $cms;

    public function __toString(){
        return $this->getName();
    }

    public function __construct()
    {
        $this->restaurants = new ArrayCollection();
        $this->admins = new ArrayCollection();
        $this->users = new ArrayCollection();
        $this->cms = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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
            $restaurant->setAdmins($this);
        }

        return $this;
    }

    public function removeRestaurant(Restaurant $restaurant): self
    {
        if ($this->restaurants->removeElement($restaurant)) {
            // set the owning side to null (unless already changed)
            if ($restaurant->getAdmins() === $this) {
                $restaurant->setAdmins(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getAdmins(): Collection
    {
        return $this->admins;
    }

    public function addAdmin(User $admin): self
    {
        if (!$this->admins->contains($admin)) {
            $this->admins[] = $admin;
            $admin->setOwnedZone($this);
        }

        return $this;
    }

    public function removeAdmin(User $admin): self
    {
        if ($this->admins->removeElement($admin)) {
            // set the owning side to null (unless already changed)
            if ($admin->getOwnedZone() === $this) {
                $admin->setOwnedZone(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->setZone($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getZone() === $this) {
                $user->setZone(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Cms[]
     */
    public function getCms(): Collection
    {
        return $this->cms;
    }

    public function addCm(Cms $cm): self
    {
        if (!$this->cms->contains($cm)) {
            $this->cms[] = $cm;
            $cm->addZone($this);
        }

        return $this;
    }

    public function removeCm(Cms $cm): self
    {
        if ($this->cms->removeElement($cm)) {
            $cm->removeZone($this);
        }

        return $this;
    }
}
