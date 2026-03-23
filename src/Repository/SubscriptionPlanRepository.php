<?php

namespace App\Repository;

use App\Entity\SubscriptionPlan;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SubscriptionPlan>
 */
class SubscriptionPlanRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SubscriptionPlan::class);
    }

    /**
     * @return SubscriptionPlan[]
     */
    public function findActivePlans(): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.active = :active')
            ->setParameter('active', true)
            ->orderBy('p.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return SubscriptionPlan[]
     */
    public function findPaginatedWithFilters(int $page, int $limit, ?string $active = null, ?string $search = null): array
    {
        $page = max(1, $page);
        $limit = max(1, min(100, $limit));

        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.createdBy', 'u')->addSelect('u')
            ->orderBy('p.id', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        if ($active !== null && $active !== '') {
            $isActive = $active === '1' || strtolower($active) === 'true' || strtolower($active) === 'active';
            $qb->andWhere('p.active = :active')
                ->setParameter('active', $isActive);
        }

        if (!empty($search)) {
            $qb->andWhere('p.name LIKE :q OR p.code LIKE :q OR p.description LIKE :q')
                ->setParameter('q', '%'.$search.'%');
        }

        return $qb->getQuery()->getResult();
    }

    public function countWithFilters(?string $active = null, ?string $search = null): int
    {
        $qb = $this->createQueryBuilder('p')
            ->select('COUNT(p.id)');

        if ($active !== null && $active !== '') {
            $isActive = $active === '1' || strtolower($active) === 'true' || strtolower($active) === 'active';
            $qb->andWhere('p.active = :active')
                ->setParameter('active', $isActive);
        }

        if (!empty($search)) {
            $qb->andWhere('p.name LIKE :q OR p.code LIKE :q OR p.description LIKE :q')
                ->setParameter('q', '%'.$search.'%');
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function getActiveCounts(?string $search = null): array
    {
        $qb = $this->createQueryBuilder('p')
            ->select('p.active AS activeFlag, COUNT(p.id) AS total')
            ->groupBy('p.active');

        if (!empty($search)) {
            $qb->andWhere('p.name LIKE :q OR p.code LIKE :q OR p.description LIKE :q')
                ->setParameter('q', '%'.$search.'%');
        }

        $rows = $qb->getQuery()->getArrayResult();
        $counts = ['active' => 0, 'inactive' => 0];
        foreach ($rows as $row) {
            if ((bool) $row['activeFlag']) {
                $counts['active'] = (int) $row['total'];
            } else {
                $counts['inactive'] = (int) $row['total'];
            }
        }

        return $counts;
    }
}
