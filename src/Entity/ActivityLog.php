<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\ActivityLogRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: ActivityLogRepository::class)]
#[ApiResource(
    operations: [new Get(), new GetCollection(), new Post(), new Put(), new Delete()],
    normalizationContext: ['groups' => ['practice_api:read']],
    denormalizationContext: ['groups' => ['practice_api:write']]
)]
class ActivityLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['practice_api:read'])]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['practice_api:read', 'practice_api:write'])]
    private ?int $userId = null;

    #[ORM\Column(length: 180)]
    #[Groups(['practice_api:read', 'practice_api:write'])]
    private string $userEmail;

    #[ORM\Column(length: 150, nullable: true)]
    #[Groups(['practice_api:read', 'practice_api:write'])]
    private ?string $username = null;

    #[ORM\Column(length: 50)]
    #[Groups(['practice_api:read', 'practice_api:write'])]
    private string $userRole;

    #[ORM\Column(length: 80)]
    #[Groups(['practice_api:read', 'practice_api:write'])]
    private string $action;

    #[ORM\Column(length: 80, nullable: true)]
    #[Groups(['practice_api:read', 'practice_api:write'])]
    private ?string $entity = null;

    #[ORM\Column(length: 80, nullable: true)]
    #[Groups(['practice_api:read', 'practice_api:write'])]
    private ?string $entityId = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    #[Groups(['practice_api:read', 'practice_api:write'])]
    private ?array $details = null;

    #[ORM\Column(length: 64, nullable: true)]
    #[Groups(['practice_api:read', 'practice_api:write'])]
    private ?string $ipAddress = null;

    #[ORM\Column]
    #[Groups(['practice_api:read', 'practice_api:write'])]
    private DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserEmail(): string
    {
        return $this->userEmail;
    }

    public function setUserEmail(string $userEmail): static
    {
        $this->userEmail = $userEmail;

        return $this;
    }

    public function getUserRole(): string
    {
        return $this->userRole;
    }

    public function setUserRole(string $userRole): static
    {
        $this->userRole = $userRole;

        return $this;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function setAction(string $action): static
    {
        $this->action = $action;

        return $this;
    }

    public function getEntity(): ?string
    {
        return $this->entity;
    }

    public function setEntity(?string $entity): static
    {
        $this->entity = $entity;

        return $this;
    }

    public function getEntityId(): ?string
    {
        return $this->entityId;
    }

    public function setEntityId(?string $entityId): static
    {
        $this->entityId = $entityId;

        return $this;
    }

    public function getDetails(): ?array
    {
        return $this->details;
    }

    public function setDetails(?array $details): static
    {
        $this->details = $details;

        return $this;
    }

    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function setIpAddress(?string $ipAddress): static
    {
        $this->ipAddress = $ipAddress;

        return $this;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId(?int $userId): static
    {
        $this->userId = $userId;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(?string $username): static
    {
        $this->username = $username;

        return $this;
    }
}

