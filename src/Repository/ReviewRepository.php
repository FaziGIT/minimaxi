<?php

namespace App\Repository;

use App\Entity\Client;
use App\Entity\Product;
use App\Entity\Review;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Review>
 */
class ReviewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Review::class);
    }

    /**
     * @param Client $client
     * @return array<Review>
     */
    public function findAllReviewsByUser(Client $client): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.client = :client')
            ->setParameter('client', $client)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Product $product
     * @return array<Review>
     */
    public function getReviewsFromProduct(Product $product): array
    {
        return $this->createQueryBuilder('r')
            ->select('r', 'l')
            ->leftJoin('r.likes', 'l')
            ->andWhere('r.product = :product')
            ->setParameter('product', $product)
            ->getQuery()
            ->getResult();
    }
}
