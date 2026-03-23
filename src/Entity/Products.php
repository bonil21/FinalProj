<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\ProductsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Category;
use App\Entity\User;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: ProductsRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post(),
        new Put(),
        new Delete()
    ],
    normalizationContext: ['groups' => ['practice_api:read']],
    denormalizationContext: ['groups' => ['practice_api:write']]
)]
class Products
{
    #[ORM\ManyToOne(targetEntity: Category::class)]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['practice_api:read', 'practice_api:write'])]
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
    #[Groups(['practice_api:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['practice_api:read', 'practice_api:write'])]
    private ?string $name = null;



    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['practice_api:read', 'practice_api:write'])]
    private ?string $description = null;

    #[ORM\Column]
    #[Groups(['practice_api:read', 'practice_api:write'])]
    private ?float $price = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['practice_api:read', 'practice_api:write'])]
    private ?string $NutritionalInfo = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['practice_api:read', 'practice_api:write'])]
    private ?string $Availability = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['practice_api:read', 'practice_api:write'])]
    private ?string $SubscriptionEligible = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['practice_api:read', 'practice_api:write'])]
    private ?string $image = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['practice_api:read', 'practice_api:write'])]
    private ?User $createdBy = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['practice_api:read', 'practice_api:write'])]
    private ?\DateTimeImmutable $createdAt = null;

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

    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $createdBy): static
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
