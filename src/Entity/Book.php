<?php

namespace App\Entity;
use App\Enum\BookStatusEnum;
use App\Enum\SectionEnum;
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

    #[ORM\Column(type:'string')] 
    private SectionEnum $section;

    #[ORM\Column(type:'string')] 
    private BookStatusEnum $bookStatus;

    #[ORM\Column(length: 255)]
    private ?string $bookCode = null;

    /**
     * @var Collection<int, Loan>
     */
    #[ORM\OneToMany(targetEntity: Loan::class, mappedBy: 'book')]
    private Collection $loans;

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

    public function setSection(SectionEnum $section)
    {
        $this->section = $section;
        return $this;
    }

    public function getSection(): SectionEnum
    {
        return $this->section;
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
}