<?php

namespace App\Entity;

use App\Enum\InventoryStatusEnum;
use App\Enum\LocationEnum;
use App\Repository\InventoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InventoryRepository::class)]
class Inventory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $Date = null;

    #[ORM\ManyToOne(inversedBy: 'inventories')]
    private ?User $User = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $Note = null;

    #[ORM\Column(nullable: true, enumType: LocationEnum::class)]
    private ?LocationEnum $Location = null;

    /**
     * @var Collection<int, InventoryItem>
     */
    #[ORM\OneToMany(targetEntity: InventoryItem::class, mappedBy: 'Inventory')]
    private Collection $inventoryItems;

    #[ORM\Column(nullable: true, enumType: InventoryStatusEnum::class)]
    private ?InventoryStatusEnum $status = null;

    public function __construct()
    {
        $this->inventoryItems = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTime
    {
        return $this->Date;
    }

    public function setDate(?\DateTime $Date): static
    {
        $this->Date = $Date;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->User;
    }

    public function setUser(?User $User): static
    {
        $this->User = $User;

        return $this;
    }

    public function getNote(): ?string
    {
        return $this->Note;
    }

    public function setNote(?string $Note): static
    {
        $this->Note = $Note;

        return $this;
    }

    public function getLocation(): ?LocationEnum
    {
        return $this->Location;
    }

    public function setLocation(?LocationEnum $Location): static
    {
        $this->Location = $Location;

        return $this;
    }

    /**
     * @return Collection<int, InventoryItem>
     */
    public function getInventoryItems(): Collection
    {
        return $this->inventoryItems;
    }

    public function addInventoryItem(InventoryItem $inventoryItem): static
    {
        if (!$this->inventoryItems->contains($inventoryItem)) {
            $this->inventoryItems->add($inventoryItem);
            $inventoryItem->setInventory($this);
        }

        return $this;
    }

    public function removeInventoryItem(InventoryItem $inventoryItem): static
    {
        if ($this->inventoryItems->removeElement($inventoryItem)) {
            // set the owning side to null (unless already changed)
            if ($inventoryItem->getInventory() === $this) {
                $inventoryItem->setInventory(null);
            }
        }

        return $this;
    }

    public function getStatus(): ?InventoryStatusEnum
    {
        return $this->status;
    }

    public function setStatus(?InventoryStatusEnum $status): static
    {
        $this->status = $status;

        return $this;
    }
}
