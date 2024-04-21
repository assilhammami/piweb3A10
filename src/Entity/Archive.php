<?php

namespace App\Entity;

use App\Repository\ArchiveRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use DateTime;

#[ORM\Entity(repositoryClass: ArchiveRepository::class)]
class Archive
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message:"le path d'image ne doit etre vide")]
    private ?string $image = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message:"la description ne doit etre vide")]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]

    
    private ?\DateTimeInterface $date_creation = null;

    #[ORM\ManyToOne(inversedBy: 'id_archives')]
    #[ORM\JoinColumn(name:"idT", referencedColumnName:"id")]
    
    private ?Travail $idT = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->date_creation;
    }

    public function setDateCreation(\DateTimeInterface $date_creation): static
    {
        $this->date_creation = $date_creation ;

        return $this;
    }

    public function getIdT(): ?Travail
    {
        return $this->idT;
    }

    public function setIdT(?Travail $idT): static
    {
        $this->idT = $idT;

        return $this;
    }

 
}
