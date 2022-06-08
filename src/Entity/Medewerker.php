<?php

namespace App\Entity;

use App\Repository\MedewerkerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: MedewerkerRepository::class)]
class Medewerker implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    private $email;

    #[ORM\Column(type: 'json')]
    private $roles = [];

    #[ORM\Column(type: 'string')]
    private $password;

    #[ORM\Column(type: 'string', length: 255)]
    private $voornaam;

    #[ORM\Column(type: 'string', length: 255)]
    private $achternaam;

    #[ORM\OneToMany(mappedBy: 'medewerker', targetEntity: Afspraak::class)]
    private $afspraaks;

    #[ORM\Column(type: 'string', length: 255)]
    private $functie;

    #[ORM\Column(type: 'integer')]
    private $telefoonnummer;

    #[ORM\OneToOne(mappedBy: 'Medewerker', targetEntity: Werkuren::class, cascade: ['persist', 'remove'])]
    private $werkuren;

    public function __construct()
    {
        $this->afspraaks = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;

        $functionRole = "ROLE_";
        $functionRole .= strtoupper($this->functie);
        array_push($roles, $functionRole);
        // guarantee every user at least has ROLE_EMPLOYEE
        $roles[] = 'ROLE_EMPLOYEE';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getVoornaam(): ?string
    {
        return $this->voornaam;
    }

    public function setVoornaam(string $voornaam): self
    {
        $this->voornaam = $voornaam;

        return $this;
    }

    public function getAchternaam(): ?string
    {
        return $this->achternaam;
    }

    public function setAchternaam(string $achternaam): self
    {
        $this->achternaam = $achternaam;

        return $this;
    }

    /**
     * @return Collection<int, Afspraak>
     */
    public function getAfspraaks(): Collection
    {
        return $this->afspraaks;
    }

    public function addAfspraak(Afspraak $afspraak): self
    {
        if (!$this->afspraaks->contains($afspraak)) {
            $this->afspraaks[] = $afspraak;
            $afspraak->setMedewerker($this);
        }

        return $this;
    }

    public function removeAfspraak(Afspraak $afspraak): self
    {
        if ($this->afspraaks->removeElement($afspraak)) {
            // set the owning side to null (unless already changed)
            if ($afspraak->getMedewerker() === $this) {
                $afspraak->setMedewerker(null);
            }
        }

        return $this;
    }

    public function getFunctie(): ?string
    {
        return $this->functie;
    }

    public function setFunctie(string $functie): self
    {
        $this->functie = $functie;

        return $this;
    }

    public function getTelefoonnummer(): ?int
    {
        return $this->telefoonnummer;
    }

    public function setTelefoonnummer(int $telefoonnummer): self
    {
        $this->telefoonnummer = $telefoonnummer;

        return $this;
    }

    public function getWerkuren(): ?Werkuren
    {
        return $this->werkuren;
    }

    public function setWerkuren(Werkuren $werkuren): self
    {
        // set the owning side of the relation if necessary
        if ($werkuren->getMedewerker() !== $this) {
            $werkuren->setMedewerker($this);
        }

        $this->werkuren = $werkuren;

        return $this;
    }
}
