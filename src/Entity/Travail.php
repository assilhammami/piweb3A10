<?php

namespace App\Entity;

use App\Repository\TravailRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;



#[ORM\Entity(repositoryClass: TravailRepository::class)]

class Travail
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message:"la description ne doit etre vide")]
    private ?string $description = null;

    #[ORM\Column]
    #[Assert\NotBlank(message:"le prix ne doit etre vide")]
    private ?int $prix = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message:"le type ne doit etre vide")]
    private ?string $type = null;

    #[ORM\Column(type: "date")]
    #[Assert\NotBlank(message:"la date de demande ne doit etre vide")]
    private ?\DateTimeInterface $date_demande = null;
    

    #[ORM\Column(type: "date")]
    #[Assert\NotBlank(message:"la date de fin ne doit etre vide")]
    
    private ?\DateTimeInterface $date_fin = null;
    

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message:"le titre ne doit etre vide")]
    private ?string $titre = null;

    #[ORM\OneToMany(targetEntity: Archive::class, mappedBy: 'idT')]
    private Collection $id_archives;


    public function __construct()
    {
    
        $this->id_archives = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getPrix(): ?int
    {
        return $this->prix;
    }

    public function setPrix(?int $prix): self
    {
        $this->prix = $prix;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getDateDemande(): ?\DateTimeInterface
    {
        return $this->date_demande;
    }

    public function setDateDemande(?\DateTimeInterface $date_demande): self
    {


        $this->date_demande = $date_demande =new DateTime('today');


        return $this;
    }

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->date_fin;
    }

    public function setDateFin(?\DateTimeInterface $date_fin): self
    {
        $this->date_fin = $date_fin;

        return $this;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(?string $titre): self
    {
        $this->titre = $titre;

        return $this;
    }

    /**
     * @return Collection<int, Archive>
     */
    public function getIdArchives(): Collection
    {
        return $this->id_archives;
    }

    public function addIdArchive(Archive $idArchive): static
    {
        if (!$this->id_archives->contains($idArchive)) {
            $this->id_archives->add($idArchive);
            $idArchive->setIdT($this);
        }

        return $this;
    }

    public function removeIdArchive(Archive $idArchive): static
    {
        if ($this->id_archives->removeElement($idArchive)) {
            // set the owning side to null (unless already changed)
            if ($idArchive->getIdT() === $this) {
                $idArchive->setIdT(null);
            }
        }

        return $this;
    }
    

    public function __toString()
    {
        return $this->getId();
    }

}
