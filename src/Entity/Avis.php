<?php

namespace App\Entity;

use App\Repository\AvisRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert; 

#[ORM\Entity(repositoryClass: AvisRepository::class)]
class Avis
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message:"le commentaire ne doit pas etre vide")]
    private ?string $commentaire = null;

    #[ORM\ManyToOne(inversedBy: 'user')]
    #[ORM\JoinColumn(name:"id_user", referencedColumnName:"id", nullable:false)]
    private ?User $id_user = null;

    #[ORM\ManyToOne(inversedBy: 'avis')]
    #[ORM\JoinColumn(name:"id_cour", referencedColumnName:"id", nullable:false)]
    private ?Cours $id_cour = null;





    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(string $commentaire): static
    {
        $this->commentaire = $commentaire;

        return $this;
    }

 

    public function __toString()
    {
        return $this->getId();
    }

    public function getIdUser(): ?User
    {
        return $this->id_user;
    }

    public function setIdUser(?User $id_user): static
    {
        $this->id_user = $id_user;

        return $this;
    }

    public function getIdCour(): ?Cours
    {
        return $this->id_cour;
    }

    public function setIdCour(?Cours $id_cour): static
    {
        $this->id_cour = $id_cour;

        return $this;
    }

}
