<?php

namespace App\Repository;

use App\Entity\ActivityLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ActivityLog>
 */
class ActivityLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ActivityLog::class);
    }

    /**
     * @return ActivityLog[]
     */
    public function findWithFilters(
        ?string $userFilter,
        ?string $roleFilter,
        ?string $actionFilter,
        ?\DateTimeImmutable $dateFrom,
        ?\DateTimeImmutable $dateTo
    ): array {
        $qb = $this->createQueryBuilder('a')
            ->orderBy('a.createdAt', 'DESC');

        if ($userFilter) {
            $qb->andWhere('a.userEmail LIKE :user')
                ->setParameter('user', '%'.$userFilter.'%');
        }

        if ($roleFilter) {
            $qb->andWhere('a.userRole = :role')
                ->setParameter('role', $roleFilter);
        }

        if ($actionFilter) {
            $qb->andWhere('a.action = :action')
                ->setParameter('action', $actionFilter);
        }

        if ($dateFrom) {
            $qb->andWhere('a.createdAt >= :dateFrom')
                ->setParameter('dateFrom', $dateFrom);
        }

        if ($dateTo) {
            $qb->andWhere('a.createdAt <= :dateTo')
                ->setParameter('dateTo', $dateTo);
        }

        return $qb->getQuery()->getResult();
    }
}

