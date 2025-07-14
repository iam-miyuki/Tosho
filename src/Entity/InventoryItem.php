<?php

namespace App\Entity;

use App\Enum\InventoryItemStatusEnum;
use App\Repository\InventoryItemRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InventoryItemRepository::class)]
class InventoryItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'inventoryItems')]
    private ?Book $Book = null;

    #[ORM\ManyToOne(inversedBy: 'inventoryItems')]
    private ?Inventory $Inventory = null;

    #[ORM\Column(nullable: true, enumType: InventoryItemStatusEnum::class)]
    private ?InventoryItemStatusEnum $Status = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $Note = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBook(): ?Book
    {
        return $this->Book;
    }

    public function setBook(?Book $Book): static
    {
        $this->Book = $Book;

        return $this;
    }

    public function getInventory(): ?Inventory
    {
        return $this->Inventory;
    }

    public function setInventory(?Inventory $Inventory): static
    {
        $this->Inventory = $Inventory;

        return $this;
    }

    public function getStatus(): ?InventoryItemStatusEnum
    {
        return $this->Status;
    }

    public function setStatus(?InventoryItemStatusEnum $Status): static
    {
        $this->Status = $Status;

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
}
