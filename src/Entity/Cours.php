<?php

namespace App\Entity;

use App\Repository\CoursRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert; 
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use DateTime;

#[ORM\Entity(repositoryClass: CoursRepository::class)]
#[UniqueEntity("nom", message:"Ce nom de cours est déjà utilisé")]

class Cours
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column()]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message:"le nom ne doit pas etre vide")]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message:"la description ne doit pas etre vide")]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date_pub = null;

    #[ORM\Column(length: 255)]
    
    private ?string $image = null;

    #[ORM\Column(type: 'integer')]
    private ?int $likes = null;

    #[ORM\Column(type: 'integer')]
    private ?int $dislikes = null;

    #[ORM\OneToMany(targetEntity: Avis::class, mappedBy: 'id_cour')]
    private Collection $avis;

    public function __construct()
    {
        $this->avis = new ArrayCollection();
    }



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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getDatePub(): ?\DateTimeInterface
    {
        return $this->date_pub;
    }

    public function setDatePub(\DateTimeInterface $date_pub): static
    {
        $this->date_pub = $date_pub = new DateTime('today');

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

    public function getLikes(): ?int
{
    return $this->likes;
}


public function setLikes(?int $likes): static
{
    $this->likes = $likes;

    return $this;
}


public function getDislikes(): ?int
{
    return $this->dislikes;
}

public function setDislikes(?int $dislikes): static
{
    $this->dislikes = $dislikes;

    return $this;
}

    /**
     * @return Collection<int, Avis>
     */
    public function getAvis(): Collection
    {
        return $this->avis;
    }

    public function addAvi(Avis $avi): static
    {
        if (!$this->avis->contains($avi)) {
            $this->avis->add($avi);
            $avi->setIdCour($this);
        }

        return $this;
    }

    public function removeAvi(Avis $avi): static
    {
        if ($this->avis->removeElement($avi)) {
            // set the owning side to null (unless already changed)
            if ($avi->getIdCour() === $this) {
                $avi->setIdCour(null);
            }
        }

        return $this;
    }

    public function __toString()
    {
        return $this->id;
    }

}
