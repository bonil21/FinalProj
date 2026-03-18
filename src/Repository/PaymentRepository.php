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
     * Calculate total revenue for today (successful payments for orders created today)
     * Includes payments with status 'completed' or 'succeeded' for orders created today
     */
    public function getTodayRevenue(): float
    {
        $today = new \DateTimeImmutable();
        $today = $today->setTime(0, 0, 0);

        $result = $this->createQueryBuilder('p')
            ->select('SUM(p.amount)')
            ->innerJoin('p.order', 'o')
            ->andWhere('p.status IN (:completedStatuses)')
            ->andWhere('o.createdAt >= :today')
            ->andWhere('p.order IS NOT NULL')
            ->setParameter('completedStatuses', ['completed', 'succeeded'])
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
            ->setParameter('completedStatuses', ['completed', 'succeeded'])
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
            ->setParameter('completedStatuses', ['completed', 'succeeded'])
            ->setParameter('startOfMonth', $startOfMonth)
            ->getQuery()
            ->getSingleScalarResult();

        return $result ? (float) $result : 0.0;
    }
}
