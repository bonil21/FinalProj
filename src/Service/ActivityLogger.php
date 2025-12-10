<?php

namespace App\Service;

use App\Entity\ActivityLog;
use App\Entity\User;
use App\Repository\ActivityLogRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class ActivityLogger
{
    public function __construct(
        private EntityManagerInterface $em,
        private RequestStack $requestStack,
    ) {}

    /**
     * @param array<string,mixed>|null $details
     */
    public function log(?User $user, string $action, ?string $entity = null, ?string $entityId = null, ?array $details = null): void
    {
        $log = new ActivityLog();

        if ($user) {
            $log->setUserId($user->getId());
            $log->setUserEmail($user->getEmail());
            $log->setUsername($user->getName() ?? $user->getEmail());
            $log->setUserRole(implode(',', $user->getRoles()));
        } else {
            $log->setUserId(null);
            $log->setUserEmail('anonymous');
            $log->setUsername('anonymous');
            $log->setUserRole('PUBLIC');
        }

        $log->setAction($action);
        $log->setEntity($entity);
        $log->setEntityId($entityId);
        $log->setDetails($details);

        $ip = $this->requestStack->getCurrentRequest()?->getClientIp();
        $log->setIpAddress($ip);

        $this->em->persist($log);
        $this->em->flush();
    }
}

