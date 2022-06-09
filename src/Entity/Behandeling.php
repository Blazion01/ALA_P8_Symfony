<?php

namespace App\Entity;

use App\Repository\BehandelingRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BehandelingRepository::class)]
class Behandeling
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer')]
    private $type;

    #[ORM\Column(type: 'string', length: 255)]
    private $groep;

    #[ORM\Column(type: 'string', length: 255)]
    private $naam;

    #[ORM\Column(type: 'decimal', precision: 4, scale: 2)]
    private $prijs;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(int $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getGroep(): ?string
    {
        return $this->groep;
    }

    public function setGroep(string $groep): self
    {
        $this->groep = $groep;

        return $this;
    }

    public function getNaam(): ?string
    {
        return $this->naam;
    }

    public function setNaam(string $naam): self
    {
        $this->naam = $naam;

        return $this;
    }

    public function getPrijs(): ?string
    {
        return $this->prijs;
    }

    public function setPrijs(string $prijs): self
    {
        $this->prijs = $prijs;

        return $this;
    }
}
