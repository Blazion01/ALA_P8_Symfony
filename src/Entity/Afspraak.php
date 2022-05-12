<?php

namespace App\Entity;

use App\Repository\AfspraakRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AfspraakRepository::class)]
class Afspraak
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'time')]
    private $tijd;

    #[ORM\Column(type: 'date')]
    private $datum;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $status;

    #[ORM\OneToOne(targetEntity: Behandeling::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private $behandeling;

    #[ORM\ManyToOne(targetEntity: Klant::class, inversedBy: 'afspraaks')]
    #[ORM\JoinColumn(nullable: false)]
    private $klant;

    #[ORM\ManyToOne(targetEntity: Medewerker::class, inversedBy: 'afspraaks')]
    private $medewerker;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTijd(): ?\DateTimeInterface
    {
        return $this->tijd;
    }

    public function setTijd(\DateTimeInterface $tijd): self
    {
        $this->tijd = $tijd;

        return $this;
    }

    public function getDatum(): ?\DateTimeInterface
    {
        return $this->datum;
    }

    public function setDatum(\DateTimeInterface $datum): self
    {
        $this->datum = $datum;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getBetaling(): ?Behandeling
    {
        return $this->behandeling;
    }

    public function setBetaling(Behandeling $behandeling): self
    {
        $this->behandeling = $behandeling;

        return $this;
    }

    public function getKlant(): ?Klant
    {
        return $this->klant;
    }

    public function setKlant(?Klant $klant): self
    {
        $this->klant = $klant;

        return $this;
    }

    public function getMedewerker(): ?Medewerker
    {
        return $this->medewerker;
    }

    public function setMedewerker(?Medewerker $medewerker): self
    {
        $this->medewerker = $medewerker;

        return $this;
    }
}
