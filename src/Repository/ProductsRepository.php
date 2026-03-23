<?php

namespace App\Repository;

use App\Entity\Products;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Products>
 */
class ProductsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Products::class);
    }

    /**
     * Lightweight featured set for landing page.
     *
     * @return Products[]
     */
    public function findFeatured(int $limit = 6): array
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Products[]
     */
    public function findWithFilters(?string $search = null, ?int $categoryId = null): array
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.category', 'c')
            ->addSelect('c')
            ->orderBy('p.createdAt', 'DESC');

        if ($search) {
            $qb->andWhere('LOWER(p.name) LIKE :search OR LOWER(p.description) LIKE :search')
                ->setParameter('search', '%'.mb_strtolower($search).'%');
        }

        if ($categoryId !== null) {
            $qb->andWhere('c.id = :categoryId')
                ->setParameter('categoryId', $categoryId);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Returns products that can be selected for subscription meals.
     *
     * @return Products[]
     */
    public function findAvailableForSubscription(): array
    {
        $products = $this->createQueryBuilder('p')
            ->orderBy('p.name', 'ASC')
            ->getQuery()
            ->getResult();

        return array_values(array_filter($products, static function (Products $product): bool {
            $availability = mb_strtolower(trim((string) $product->getAvailability()));
            $eligible = mb_strtolower(trim((string) $product->getSubscriptionEligible()));

            $isAvailable = $availability === ''
                || (!str_contains($availability, 'out') && !str_contains($availability, 'unavailable'));
            $isEligible = $eligible === ''
                || str_contains($eligible, 'yes')
                || str_contains($eligible, 'true')
                || str_contains($eligible, 'eligible');

            return $isAvailable && $isEligible;
        }));
    }

    //    /**
    //     * @return Products[] Returns an array of Products objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Products
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
