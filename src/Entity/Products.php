<?php

namespace App\Entity;

use App\Repository\ProductsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Category;

#[ORM\Entity(repositoryClass: ProductsRepository::class)]
class Products
{
    #[ORM\ManyToOne(targetEntity: Category::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Category $category = null;

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;
        return $this;
    }

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;



    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column]
    private ?float $price = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $NutritionalInfo = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $Availability = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $SubscriptionEligible = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

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

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getNutritionalInfo(): ?string
    {
        return $this->NutritionalInfo;
    }

    public function setNutritionalInfo(string $NutritionalInfo): static
    {
        $this->NutritionalInfo = $NutritionalInfo;

        return $this;
    }

    public function getAvailability(): ?string
    {
        return $this->Availability;
    }

    public function setAvailability(string $Availability): static
    {
        $this->Availability = $Availability;

        return $this;
    }

    public function getSubscriptionEligible(): ?string
    {
        return $this->SubscriptionEligible;
    }

    public function setSubscriptionEligible(string $SubscriptionEligible): static
    {
        $this->SubscriptionEligible = $SubscriptionEligible;

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
}
