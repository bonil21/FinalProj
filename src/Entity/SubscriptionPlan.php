<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\SubscriptionPlanRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\User;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: SubscriptionPlanRepository::class)]
#[ApiResource(
    operations: [new Get(), new GetCollection(), new Post(), new Put(), new Delete()],
    normalizationContext: ['groups' => ['practice_api:read']],
    denormalizationContext: ['groups' => ['practice_api:write']]
)]
class SubscriptionPlan
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['practice_api:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    #[Groups(['practice_api:read', 'practice_api:write'])]
    private ?string $code = null;

    #[ORM\Column(length: 150)]
    #[Groups(['practice_api:read', 'practice_api:write'])]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['practice_api:read', 'practice_api:write'])]
    private ?string $description = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Groups(['practice_api:read', 'practice_api:write'])]
    private ?string $price = null;

    #[ORM\Column(length: 10)]
    #[Groups(['practice_api:read', 'practice_api:write'])]
    private ?string $billingInterval = null;

    #[ORM\Column]
    #[Groups(['practice_api:read', 'practice_api:write'])]
    private ?int $mealsIncluded = null;

    #[ORM\Column]
    #[Groups(['practice_api:read', 'practice_api:write'])]
    private ?bool $active = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['practice_api:read', 'practice_api:write'])]
    private ?User $createdBy = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

        return $this;
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

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getBillingInterval(): ?string
    {
        return $this->billingInterval;
    }

    public function setBillingInterval(string $billingInterval): static
    {
        $this->billingInterval = $billingInterval;

        return $this;
    }

    public function getMealsIncluded(): ?int
    {
        return $this->mealsIncluded;
    }

    public function setMealsIncluded(int $mealsIncluded): static
    {
        $this->mealsIncluded = $mealsIncluded;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;

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
}
