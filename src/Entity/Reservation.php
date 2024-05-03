<?php

namespace App\Entity;
use App\Repository\ReservationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReservationRepository::class)]
class Reservation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $date = null;
    #[ORM\Column]
    private ?int $nbplaces= null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: "idevent", referencedColumnName: "id", nullable: false)]
    private ?Event $idevent = null;
    

   
    public function getId(): ?int
    {
        return $this->id;
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

    public function getNbplaces(): ?int
    {
        return $this->nbplaces;
    }

    public function setNbplaces(int $nbplaces): static
    {
        $this->nbplaces = $nbplaces;

        return $this;
    }

    public function getIdevent(): ?Event
    {
        return $this->idevent;
    }

    public function setIdevent(?Event $idevent): static
    {
        $this->idevent = $idevent;

        return $this;
    }

    

    

}
