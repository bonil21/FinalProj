<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\SubscriptionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\User;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: SubscriptionRepository::class)]
#[ApiResource(
    operations: [new Get(), new GetCollection(), new Post(), new Put(), new Delete()],
    normalizationContext: ['groups' => ['practice_api:read']],
    denormalizationContext: ['groups' => ['practice_api:write']]
)]
class Subscription
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['practice_api:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'subscriptions')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['practice_api:read', 'practice_api:write'])]
    private ?Customer $customer = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['practice_api:read', 'practice_api:write'])]
    private ?SubscriptionPlan $plan = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['practice_api:read', 'practice_api:write'])]
    private ?User $createdBy = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['practice_api:read', 'practice_api:write'])]
    private ?string $stripeSubscriptionId = null;

    #[ORM\Column(length: 20)]
    #[Groups(['practice_api:read', 'practice_api:write'])]
    private ?string $status = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Groups(['practice_api:read', 'practice_api:write'])]
    private ?\DateTime $startDate = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['practice_api:read', 'practice_api:write'])]
    private ?\DateTime $currentPeriodStart = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['practice_api:read', 'practice_api:write'])]
    private ?\DateTime $currentPeriodEnd = null;

    #[ORM\Column]
    #[Groups(['practice_api:read', 'practice_api:write'])]
    private ?bool $cancelAtPeriodEnd = false;

    #[ORM\Column]
    #[Groups(['practice_api:read', 'practice_api:write'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    #[Groups(['practice_api:read', 'practice_api:write'])]
    private ?array $selectedMeals = null;

    public function __construct()
    {
        $this->cancelAtPeriodEnd = false;
        $this->createdAt = new \DateTimeImmutable();
        $this->selectedMeals = [];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    public function setCustomer(?Customer $customer): static
    {
        $this->customer = $customer;

        return $this;
    }

    public function getPlan(): ?SubscriptionPlan
    {
        return $this->plan;
    }

    public function setPlan(?SubscriptionPlan $plan): static
    {
        $this->plan = $plan;

        return $this;
    }

    public function getStripeSubscriptionId(): ?string
    {
        return $this->stripeSubscriptionId;
    }

    public function setStripeSubscriptionId(?string $stripeSubscriptionId): static
    {
        $this->stripeSubscriptionId = $stripeSubscriptionId;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getStartDate(): ?\DateTime
    {
        return $this->startDate;
    }

    public function setStartDate(?\DateTime $startDate): static
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getCurrentPeriodStart(): ?\DateTime
    {
        return $this->currentPeriodStart;
    }

    public function setCurrentPeriodStart(?\DateTime $currentPeriodStart): static
    {
        $this->currentPeriodStart = $currentPeriodStart;

        return $this;
    }

    public function getCurrentPeriodEnd(): ?\DateTime
    {
        return $this->currentPeriodEnd;
    }

    public function setCurrentPeriodEnd(?\DateTime $currentPeriodEnd): static
    {
        $this->currentPeriodEnd = $currentPeriodEnd;

        return $this;
    }

    public function isCancelAtPeriodEnd(): ?bool
    {
        return $this->cancelAtPeriodEnd;
    }

    public function setCancelAtPeriodEnd(bool $cancelAtPeriodEnd): static
    {
        $this->cancelAtPeriodEnd = $cancelAtPeriodEnd;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

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

    public function getSelectedMeals(): array
    {
        return $this->selectedMeals ?? [];
    }

    public function setSelectedMeals(?array $selectedMeals): static
    {
        $this->selectedMeals = $selectedMeals ?? [];

        return $this;
    }
}
