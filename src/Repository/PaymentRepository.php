<?php

namespace App\Repository;

use App\Entity\Payment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Payment>
 */
class PaymentRepository extends ServiceEntityRepository
{
    private const SUCCESS_STATUSES = ['completed', 'succeeded', 'success', 'paid'];

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Payment::class);
    }

    public function save(Payment $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Payment $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Payment[] Returns an array of Payment objects
     */
    public function findByCustomer($customer): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.customer = :customer')
            ->setParameter('customer', $customer)
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Payment[] Returns an array of Payment objects
     */
    public function findByStatus($status): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.status = :status')
            ->setParameter('status', $status)
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Calculate total revenue for today from successful payments.
     * Uses paidAt when available, otherwise falls back to createdAt.
     */
    public function getTodayRevenue(): float
    {
        $today = new \DateTimeImmutable();
        $today = $today->setTime(0, 0, 0);

        $result = $this->createQueryBuilder('p')
            ->select('SUM(p.amount)')
            ->andWhere('p.status IN (:completedStatuses)')
            ->andWhere('(p.paidAt >= :today OR (p.paidAt IS NULL AND p.createdAt >= :today))')
            ->setParameter('completedStatuses', self::SUCCESS_STATUSES)
            ->setParameter('today', $today)
            ->getQuery()
            ->getSingleScalarResult();

        return $result ? (float) $result : 0.0;
    }

    /**
     * Calculate total revenue (all successful payments)
     * Includes payments with status 'completed' or 'succeeded'
     */
    public function getTotalRevenue(): float
    {
        $result = $this->createQueryBuilder('p')
            ->select('SUM(p.amount)')
            ->andWhere('p.status IN (:completedStatuses)')
            ->setParameter('completedStatuses', self::SUCCESS_STATUSES)
            ->getQuery()
            ->getSingleScalarResult();

        return $result ? (float) $result : 0.0;
    }

    /**
     * Calculate total revenue for current month (successful payments)
     * Includes payments with status 'completed' or 'succeeded' created in current month
     */
    public function getMonthlyRevenue(): float
    {
        $startOfMonth = new \DateTime('first day of this month');
        $startOfMonth->setTime(0, 0, 0);

        $result = $this->createQueryBuilder('p')
            ->select('SUM(p.amount)')
            ->andWhere('p.status IN (:completedStatuses)')
            ->andWhere('p.createdAt >= :startOfMonth')
            ->setParameter('completedStatuses', self::SUCCESS_STATUSES)
            ->setParameter('startOfMonth', $startOfMonth)
            ->getQuery()
            ->getSingleScalarResult();

        return $result ? (float) $result : 0.0;
    }

    /**
     * @return Payment[]
     */
    public function findPaginatedWithFilters(int $page, int $limit, ?string $status = null, ?string $search = null): array
    {
        $page = max(1, $page);
        $limit = max(1, min(100, $limit));

        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.customer', 'c')
            ->addSelect('c')
            ->orderBy('p.createdAt', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        if (!empty($status)) {
            $qb->andWhere('p.status = :status')
                ->setParameter('status', $status);
        }

        if (!empty($search)) {
            $qb->andWhere('c.name LIKE :q OR c.email LIKE :q OR p.paymentMethod LIKE :q OR p.description LIKE :q')
                ->setParameter('q', '%'.$search.'%');
        }

        return $qb->getQuery()->getResult();
    }

    public function countWithFilters(?string $status = null, ?string $search = null): int
    {
        $qb = $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->leftJoin('p.customer', 'c');

        if (!empty($status)) {
            $qb->andWhere('p.status = :status')
                ->setParameter('status', $status);
        }

        if (!empty($search)) {
            $qb->andWhere('c.name LIKE :q OR c.email LIKE :q OR p.paymentMethod LIKE :q OR p.description LIKE :q')
                ->setParameter('q', '%'.$search.'%');
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function getStatusCounts(?string $search = null): array
    {
        $qb = $this->createQueryBuilder('p')
            ->select('p.status AS status, COUNT(p.id) AS total')
            ->leftJoin('p.customer', 'c')
            ->groupBy('p.status');

        if (!empty($search)) {
            $qb->andWhere('c.name LIKE :q OR c.email LIKE :q OR p.paymentMethod LIKE :q OR p.description LIKE :q')
                ->setParameter('q', '%'.$search.'%');
        }

        $rows = $qb->getQuery()->getArrayResult();
        $counts = [];
        foreach ($rows as $row) {
            $counts[$row['status']] = (int) $row['total'];
        }

        return $counts;
    }

    /**
     * Returns order IDs that have a completed online payment (GCash/ATM).
     *
     * @return int[]
     */
    public function findCompletedOnlineOrderIds(): array
    {
        $rows = $this->createQueryBuilder('p')
            ->select('DISTINCT IDENTITY(p.order) AS orderId')
            ->andWhere('p.order IS NOT NULL')
            ->andWhere('p.paymentMethod IN (:methods)')
            ->andWhere('p.status IN (:completedStatuses)')
            ->setParameter('methods', ['gcash', 'atm'])
            ->setParameter('completedStatuses', self::SUCCESS_STATUSES)
            ->getQuery()
            ->getArrayResult();

        return array_values(array_filter(array_map(
            static fn (array $row): ?int => isset($row['orderId']) ? (int) $row['orderId'] : null,
            $rows
        )));
    }
}
