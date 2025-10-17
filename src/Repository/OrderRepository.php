<?php

namespace App\Repository;

use App\Entity\Order;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Order>
 */
class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    /**
     * @return Order[] Returns an array of Order objects ordered by creation date
     */
    public function findAllOrderedByCreatedAt(): array
    {
        return $this->createQueryBuilder('o')
            ->orderBy('o.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findOneByOrderNumber(string $orderNumber): ?Order
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.orderNumber = :orderNumber')
            ->setParameter('orderNumber', $orderNumber)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return Order[] Returns orders by status
     */
    public function findByStatus(string $status): array
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.status = :status')
            ->setParameter('status', $status)
            ->orderBy('o.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Count orders created today
     */
    public function countOrdersToday(): int
    {
        $today = new \DateTime();
        $today->setTime(0, 0, 0);
        
        return $this->createQueryBuilder('o')
            ->select('COUNT(o.id)')
            ->andWhere('o.createdAt >= :today')
            ->setParameter('today', $today)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Calculate total revenue for today
     */
    public function getTodayRevenue(): float
    {
        $today = new \DateTime();
        $today->setTime(0, 0, 0);
        
        $result = $this->createQueryBuilder('o')
            ->select('SUM(o.totalAmount)')
            ->andWhere('o.createdAt >= :today')
            ->setParameter('today', $today)
            ->getQuery()
            ->getSingleScalarResult();
            
        return $result ? (float) $result : 0.0;
    }

    /**
     * Count active subscriptions (orders with active status)
     */
    public function countActiveSubscriptions(): int
    {
        return $this->createQueryBuilder('o')
            ->select('COUNT(o.id)')
            ->andWhere('o.status IN (:activeStatuses)')
            ->setParameter('activeStatuses', ['pending', 'processing', 'shipped'])
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Get recent orders for dashboard
     */
    public function findRecentOrders(int $limit = 5): array
    {
        return $this->createQueryBuilder('o')
            ->orderBy('o.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
