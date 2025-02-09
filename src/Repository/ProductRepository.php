<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    /**
     * @param int $limit
     * @return array<Product>
     */
    public function findMostRecentProducts(int $limit): array
    {
        return $this->createQueryBuilder('p')
            ->select('p.id, p.name,p.price, MIN(img.url) as imageProducts')
            ->leftJoin('p.imageProducts', 'img')
            ->orderBy('p.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->groupBy('p.id')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param int $limit
     * @return array<int, array<string, mixed>> Returns an array of top-rated products, each containing:
     */
    public function findTopRatedProducts(int $limit): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.reviews', 'r')
            ->leftJoin('p.imageProducts', 'img')
            ->select('p.id, p.name, p.price, MIN(img.url) AS firstImage, AVG(r.rating) AS avgRating')
            ->groupBy('p.id')
            ->orderBy('avgRating', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * @return array<Product>
     */
    public function findAllOptimized(): array
    {
        return $this->createQueryBuilder('p')
            ->select('p', 'c')
            ->leftJoin('p.category', 'c')
            ->getQuery()
            ->getResult();
    }
}
