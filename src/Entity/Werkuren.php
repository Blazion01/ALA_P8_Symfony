<?php

namespace App\Entity;

use App\Repository\WerkurenRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WerkurenRepository::class)]
class Werkuren
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\OneToOne(inversedBy: 'werkuren', targetEntity: Medewerker::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private $Medewerker;

    #[ORM\Column(type: 'json')]
    private $hours = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMedewerker(): ?Medewerker
    {
        return $this->Medewerker;
    }

    public function setMedewerker(Medewerker $Medewerker): self
    {
        $this->Medewerker = $Medewerker;

        return $this;
    }

    public function getHours(): ?array
    {
        return $this->hours;
    }

    public function setHours(array $hours): self
    {
        $this->hours = $hours;

        return $this;
    }
}
