<?php

namespace App\Repository;

use App\Entity\Transaction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Transaction>
 */
class TransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transaction::class);
    }

    public function save(Transaction $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Transaction $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Transaction[] Returns an array of Transaction objects
     */
    public function findByCustomer($customer): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.customer = :customer')
            ->setParameter('customer', $customer)
            ->orderBy('t.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Transaction[] Returns an array of Transaction objects
     */
    public function findByType($type): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.type = :type')
            ->setParameter('type', $type)
            ->orderBy('t.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Transaction[] Returns an array of Transaction objects
     */
    public function findByStatus($status): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.status = :status')
            ->setParameter('status', $status)
            ->orderBy('t.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Transaction[]
     */
    public function findPaginatedWithFilters(int $page, int $limit, ?string $status = null, ?string $search = null): array
    {
        $page = max(1, $page);
        $limit = max(1, min(100, $limit));

        $qb = $this->createQueryBuilder('t')
            ->leftJoin('t.customer', 'c')
            ->addSelect('c')
            ->orderBy('t.createdAt', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        if (!empty($status)) {
            $qb->andWhere('t.status = :status')
                ->setParameter('status', $status);
        }

        if (!empty($search)) {
            $qb->andWhere('c.name LIKE :q OR c.email LIKE :q OR t.reference LIKE :q OR t.type LIKE :q OR t.description LIKE :q')
                ->setParameter('q', '%'.$search.'%');
        }

        return $qb->getQuery()->getResult();
    }

    public function countWithFilters(?string $status = null, ?string $search = null): int
    {
        $qb = $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->leftJoin('t.customer', 'c');

        if (!empty($status)) {
            $qb->andWhere('t.status = :status')
                ->setParameter('status', $status);
        }

        if (!empty($search)) {
            $qb->andWhere('c.name LIKE :q OR c.email LIKE :q OR t.reference LIKE :q OR t.type LIKE :q OR t.description LIKE :q')
                ->setParameter('q', '%'.$search.'%');
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function getStatusCounts(?string $search = null): array
    {
        $qb = $this->createQueryBuilder('t')
            ->select('t.status AS status, COUNT(t.id) AS total')
            ->leftJoin('t.customer', 'c')
            ->groupBy('t.status');

        if (!empty($search)) {
            $qb->andWhere('c.name LIKE :q OR c.email LIKE :q OR t.reference LIKE :q OR t.type LIKE :q OR t.description LIKE :q')
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
