<?php

namespace App\Entity;

use App\Enum\LoanStatusEnum;
use App\Repository\LoanRepository;
use DateInterval;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

use function Symfony\Component\Clock\now;

#[ORM\Entity(repositoryClass: LoanRepository::class)]
class Loan
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue] // permet de générer automatiquement l'ID
    protected int $id;

    #[ORM\JoinColumn(name: 'family_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: Family::class)]
    private Family $family;

    #[ORM\Column(type: 'datetime')]
    private DateTime $loanDate;

    #[ORM\Column(type: 'datetime')]
    private DateTime $expectedReturnDate;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private DateTime $returnDate;

    #[ORM\ManyToOne(inversedBy: 'loans')]
    private ?Book $book = null;

    #[ORM\ManyToOne(inversedBy: 'loans')]
    private ?User $user = null;

    #[ORM\Column(nullable: true, enumType: LoanStatusEnum::class)]
    private ?LoanStatusEnum $status = null;



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
        $this->loanDate = clone $loanDate; // clone : stocker $loanDate permet de modifier sans perdre la date d'emprunt
        $this->setExpectedReturnDate($loanDate);
        return $this;
    }

    public function getLoanDate(): DateTime
    {
        return $this->loanDate;
    }
    public function setExpectedReturnDate(DateTime $loanDate)
    {
        $this->expectedReturnDate = (clone $loanDate)->add(new DateInterval('P21D')); // 'P21D' signifie Period 21 Days. ici on modifie clone de $loanDate.
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

    public function getBook(): ?Book
    {
        return $this->book;
    }

    public function setBook(?Book $book): static
    {
        $this->book = $book;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getStatus(): ?LoanStatusEnum
    {
        return $this->status;
    }

    public function setStatus(?LoanStatusEnum $status): static
    {
        if ($this->getStatus() === LoanStatusEnum::inProgress) {
            if ($this->getExpectedReturnDate() < new DateTimeImmutable('now')) {
                $this->status = LoanStatusEnum::overdue;
                return $this;
            }
        }
        $this->status = $status;

        return $this;
    }
}
