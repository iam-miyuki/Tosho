<?php

namespace App\Entity;

use App\Enum\LoanStatusEnum;
use DateInterval;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity()]
class Loan
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue] // permet de générer automatiquement l'ID
    protected int $id;

    #[ORM\JoinColumn(name: 'family_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: Family::class)]
    private Family $family;

    //TODO : à mettre propriété librarien + setter/getter

    #[ORM\Column(type: 'datetime')]
    private DateTime $loanDate;

    #[ORM\Column(type: 'datetime')]
    private DateTime $expectedReturnDate;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private DateTime $returnDate;

    #[ORM\Column(type: 'string', enumType: LoanStatusEnum::class)]
    private LoanStatusEnum $loanStatus;

    #[ORM\ManyToOne(inversedBy: 'loans')]
    private ?Book $book = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function setFamily(Family $family)
    {
        $this->family = $family;
        return $this;
    }
    public function getFamily(): Family
    {
        return $this->family;
    }

    public function setLoanDate(DateTime $loanDate)
    {
        $this->loanDate = $loanDate;
        $this->setExpectedReturnDate($loanDate);
        return $this;
    }

    public function getLoanDate(): DateTime
    {
        return $this->loanDate;
    }
    //TODO : à corriger setExpectedReturnDate
    public function setExpectedReturnDate(DateTime $loanDate)
    {
        $this->expectedReturnDate = $loanDate->add(new DateInterval("21 days"));
        return $this;
    }

    public function getExpectedReturnDate(): DateTime
    {
        return $this->expectedReturnDate;
    }

    public function setReturnDate(DateTime $returnDate)
    {
        $this->returnDate = $returnDate;
        return $this;
    }

    public function getReturnDate(): DateTime
    {
        return $this->returnDate;
    }

    public function setLoanStatus(LoanStatusEnum $loanStatus)
    {
        $this->loanStatus = $loanStatus;
        return $this;
    }

    public function getLoanStatus(): LoanStatusEnum
    {
        return $this->loanStatus;
    }

    public function getBook(): ?Book
    {
        return $this->book;
    }

    public function setBook(?Book $book): static
    {
        $this->book = $book;

        return $this;
    }

    
}
