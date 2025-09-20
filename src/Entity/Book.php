<?php

namespace App\Entity;

use App\Enum\BookStatusEnum;
use App\Enum\LocationEnum;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity()]
class Book
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue] // permet de générer automatiquement l'ID
    protected int $id;

    #[ORM\Column(type:'string')]
    private string $title;

    #[ORM\Column(type:'string')]
    private string $author;

    #[ORM\Column(type:'string', nullable: true)]
    private ?string $coverUrl;

    #[ORM\Column(type:'string', enumType: LocationEnum::class)]
    private LocationEnum $location;


    /**
     * @var Collection<int, Loan>
     */
    #[ORM\OneToMany(targetEntity: Loan::class, mappedBy: 'book')]
    private Collection $loans;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $jpTitle = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $jpAuthor = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $addedAt = null;

    /**
     * @var Collection<int, InventoryItem>
     */
    #[ORM\OneToMany(targetEntity: InventoryItem::class, mappedBy: 'book')]
    private Collection $inventoryItems;
    #[ORM\Column(nullable: true, enumType: BookStatusEnum::class)]
    private ?BookStatusEnum $status = null;
    #[ORM\Column(length: 255, nullable: true, unique: true)]
    private ?string $code = null;


    public function __construct()
    {
        $this->inventoryItems = new ArrayCollection();
    }






    public function getId(): int
    {
        return $this->id;
    }


    public function setTitle(string $title)
    {
        $this->title = $title;
        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setAuthor(string $author)
    {
        $this->author = $author;
        return $this;
    }

    public function getAuthor(): string
    {
        return $this->author;
    }

    public function setCoverUrl(?string $coverUrl)
    {
        $this->coverUrl = $coverUrl;
        return $this;
    }

    public function getCoverUrl(): ?string
    {
        return $this->coverUrl;
    }

    public function setlocation(LocationEnum $location)
    {
        $this->location = $location;
        return $this;
    }

    public function getlocation(): LocationEnum
    {
        return $this->location;
    }


    /**
     * @return Collection<int, Loan>
     */
    public function getLoans(): Collection
    {
        return $this->loans;
    }

    public function addLoan(Loan $loan): static
    {
        if (!$this->loans->contains($loan)) {
            $this->loans->add($loan);
            $loan->setBook($this);
        }

        return $this;
    }

    public function removeLoan(Loan $loan): static
    {
        if ($this->loans->removeElement($loan)) {
            // set the owning side to null (unless already changed)
            if ($loan->getBook() === $this) {
                $loan->setBook(null);
            }
        }

        return $this;
    }

    public function getJpTitle(): ?string
    {
        return $this->jpTitle;
    }

    public function setJpTitle(?string $jpTitle): static
    {
        $this->jpTitle = $jpTitle;

        return $this;
    }

    public function getJpAuthor(): ?string
    {
        return $this->jpAuthor;
    }

    public function setJpAuthor(?string $jpAuthor): static
    {
        $this->jpAuthor = $jpAuthor;

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
            $inventoryItem->setBook($this);
        }

        return $this;
    }

    public function removeInventoryItem(InventoryItem $inventoryItem): static
    {
        if ($this->inventoryItems->removeElement($inventoryItem)) {
            // set the owning side to null (unless already changed)
            if ($inventoryItem->getBook() === $this) {
                $inventoryItem->setBook(null);
            }
        }

        return $this;
    }

    public function getAddedAt(): ?\DateTimeImmutable
    {
        return $this->addedAt;
    }

    public function setAddedAt(?\DateTimeImmutable $addedAt): static
    {
        $this->addedAt = $addedAt;

        return $this;
    }

    public function getStatus(): ?BookStatusEnum
    {
        return $this->status;
    }

    public function setStatus(?BookStatusEnum $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): static
    {
        $this->code = $code;
        return $this;
    }
}
