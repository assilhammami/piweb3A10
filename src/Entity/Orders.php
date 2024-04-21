<?php

namespace App\Entity;

use App\Repository\OrdersRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrdersRepository::class)]
class Orders
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $Order_date = null;

    #[ORM\Column]
    private ?float $Total_price = null;

    #[ORM\Column]
    private ?int $Quantity = null;

    #[ORM\Column(length: 255)]
    private ?string $Product_name = null;

    #[ORM\Column(length: 255)]
    private ?string $User_name = null;
    
    #[ORM\ManyToOne(targetEntity: Products::class)]
    #[ORM\JoinColumn(nullable: false)]
    private $products;

    public function getProducts(): ?Products
    {
        return $this->products;
    }

    public function setProducts(?Products $products): self
    {
        $this->products = $products;
        return $this;
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrderDate(): ?string
    {
        return $this->Order_date;
    }

    public function setOrderDate(string $Order_date): static
    {
        $this->Order_date = $Order_date;

        return $this;
    }

    public function getTotalPrice(): ?float
    {
        return $this->Total_price;
    }

    public function setTotalPrice(float $Total_price): static
    {
        $this->Total_price = $Total_price;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->Quantity;
    }

    public function setQuantity(int $Quantity): static
    {
        $this->Quantity = $Quantity;

        return $this;
    }

    public function getProductName(): ?string
    {
        return $this->Product_name;
    }

    public function setProductName(string $Product_name): static
    {
        $this->Product_name = $Product_name;

        return $this;
    }

    public function getUserName(): ?string
    {
        return $this->User_name;
    }

    public function setUserName(string $User_name): static
    {
        $this->User_name = $User_name;

        return $this;
    }
}
