<?php

namespace App\Repository;

use App\Entity\Subscription;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Subscription>
 */
class SubscriptionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Subscription::class);
    }

    /**
     * @return Subscription[]
     */
    public function findPaginatedWithFilters(int $page, int $limit, ?string $status = null, ?string $search = null): array
    {
        $page = max(1, $page);
        $limit = max(1, min(100, $limit));

        $qb = $this->createQueryBuilder('s')
            ->leftJoin('s.customer', 'c')->addSelect('c')
            ->leftJoin('s.plan', 'p')->addSelect('p')
            ->leftJoin('s.createdBy', 'u')->addSelect('u')
            ->orderBy('s.createdAt', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        if (!empty($status)) {
            $qb->andWhere('s.status = :status')
                ->setParameter('status', $status);
        }

        if (!empty($search)) {
            $qb->andWhere('c.name LIKE :q OR c.email LIKE :q OR p.name LIKE :q OR s.stripeSubscriptionId LIKE :q')
                ->setParameter('q', '%'.$search.'%');
        }

        return $qb->getQuery()->getResult();
    }

    public function countWithFilters(?string $status = null, ?string $search = null): int
    {
        $qb = $this->createQueryBuilder('s')
            ->select('COUNT(s.id)')
            ->leftJoin('s.customer', 'c')
            ->leftJoin('s.plan', 'p');

        if (!empty($status)) {
            $qb->andWhere('s.status = :status')
                ->setParameter('status', $status);
        }

        if (!empty($search)) {
            $qb->andWhere('c.name LIKE :q OR c.email LIKE :q OR p.name LIKE :q OR s.stripeSubscriptionId LIKE :q')
                ->setParameter('q', '%'.$search.'%');
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function getStatusCounts(?string $search = null): array
    {
        $qb = $this->createQueryBuilder('s')
            ->select('s.status AS status, COUNT(s.id) AS total')
            ->leftJoin('s.customer', 'c')
            ->leftJoin('s.plan', 'p')
            ->groupBy('s.status');

        if (!empty($search)) {
            $qb->andWhere('c.name LIKE :q OR c.email LIKE :q OR p.name LIKE :q OR s.stripeSubscriptionId LIKE :q')
                ->setParameter('q', '%'.$search.'%');
        }

        $rows = $qb->getQuery()->getArrayResult();
        $counts = [];
        foreach ($rows as $row) {
            $counts[$row['status']] = (int) $row['total'];
        }

        return $counts;
    }
}
