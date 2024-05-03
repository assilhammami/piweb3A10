<?php

namespace App\Entity;
use App\Repository\EventRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass:EventRepository::class)]
class Event
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\Length(max:255, maxMessage:"Le nom ne peut pas dépasser {{ limit }} caractères")]
    #[Assert\NotBlank(message:"Le nom est requis")]
    
    private ?string $nom = null;


    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message:"La date est requise")]
   
    //#[Assert\GreaterThanOrEqual("today", message:"La date doit être aujourd'hui ou dans le futur")]
    private ?string $date = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\Column]
    #[Assert\NotBlank(message:"La capacité est requise")]
    #[Assert\PositiveOrZero(message:"La capacité doit être un nombre positif ou zéro")]
    
    private ?int $capacity = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message:"L'emplacement est requis")]
    private ?string $place = null;
    #[ORM\Column(length: 1000)]
    #[Assert\NotBlank(message:"L'image est requise")]
    private ?string $image = null;

   
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getDate(): ?string
    {
        return $this->date;
    }

    public function setDate(string $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getCapacity(): ?int
    {
        return $this->capacity;
    }

    public function setCapacity(int $capacity): static
    {
        $this->capacity = $capacity;

        return $this;
    }

    public function getPlace(): ?string
    {
        return $this->place;
    }

    public function setPlace(string $place): static
    {
        $this->place = $place;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function __toString(): string
    {
        // Retourne une représentation textuelle de l'objet Event.
        // Vous pouvez choisir d'afficher une ou plusieurs propriétés de l'objet, selon ce qui est le plus pertinent.
        // Par exemple, vous pourriez retourner une combinaison du nom et de la date.
        return sprintf("Événement: %s le %s", $this->nom, $this->date);
    }
}
