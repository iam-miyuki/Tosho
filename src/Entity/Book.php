<?php

namespace App\Entity;
use App\Enum\BookStatusEnum;
use App\Enum\LocationEnum;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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

    #[ORM\Column(type:'string')]
    private string $coverUrl;

    #[ORM\Column(type:'string', enumType: LocationEnum::class)] 
    private LocationEnum $location;

    #[ORM\Column(type: 'string', enumType: BookStatusEnum::class)]
    private BookStatusEnum $bookStatus;

    #[ORM\Column(length: 255)]
    private ?string $bookCode = null;

    /**
     * @var Collection<int, Loan>
     */
    #[ORM\OneToMany(targetEntity: Loan::class, mappedBy: 'book')]
    private Collection $loans;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $jpTitle = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $jpAuthor = null;

    public function __construct()
    {
        $this->loans = new ArrayCollection();
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

    public function setCoverUrl(string $coverUrl)
    {
        $this->coverUrl = $coverUrl;
        return $this;
    }

    public function getCoverUrl(): string
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

    public function setBookStatus(BookStatusEnum $bookStatus)
    {
        $this->bookStatus = $bookStatus;
        return $this;
    }

    public function getBookStatus(): BookStatusEnum
    {
        return $this->bookStatus;
    }


    public function getBookCode(): ?string
    {
        return $this->bookCode;
    }

    public function setBookCode(string $bookCode): static
    {
        $this->bookCode = $bookCode;

        return $this;
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
}