<?php

namespace App\Entity;

use App\Repository\WhatsappNotifRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=WhatsappNotifRepository::class)
 */
class WhatsappNotif
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $text;

    /**
     * @ORM\OneToOne(targetEntity=Order::class, cascade={"persist", "remove"})
     */
    private $order_id;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(string $text): self
    {
        $this->text = $text;

        return $this;
    }

    public function getReclamation(): ?CustomerOrder
    {
        return $this->order_id;
    }

        public function setReclamation(?CustomerOrder $order_id): self
    {
        $this->order_id = $order_id;

        return $this;
    }
}