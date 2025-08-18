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
    private ?string $firstName = null;


    #[ORM\ManyToOne(inversedBy: 'members')]
    #[ORM\JoinColumn(nullable:false, onDelete:"CASCADE")] // un membre doit toujours appartenir à une famille et quand la famille est supprimé, les membres liés sont supprimés aussi
    private ?family $family = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $jpFirstName = null;


    public function getId(): ?int
    {
        return $this->id;
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



    public function getFamily(): ?family
    {
        return $this->family;
    }

    public function setFamily(?family $family): static
    {
        $this->family = $family;

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
}
