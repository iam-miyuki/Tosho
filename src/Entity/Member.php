<?php

namespace App\Entity;

use App\Repository\MemberRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MemberRepository::class)]
class Member
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $lastName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $firstName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $jpLastName = null;

    
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $jpFirstName = null;

    #[ORM\ManyToOne(inversedBy: 'members')]
    private ?family $family = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }


    public function getJpLastName(): ?string
    {
        return $this->jpLastName;
    }

    public function setJpLastName(?string $jpLastName): static
    {
        $this->jpLastName = $jpLastName;

        return $this;
    }

   
    public function getJpFirstName(): ?string
    {
        return $this->jpFirstName;
    }

    public function setJpFirstName(?string $jpFirstName): static
    {
        $this->jpFirstName = $jpFirstName;

        return $this;
    }

    public function getFamily(): ?family
    {
        return $this->family;
    }

    public function setFamily(?family $family): static
    {
        $this->family = $family;

        return $this;
    }

}
