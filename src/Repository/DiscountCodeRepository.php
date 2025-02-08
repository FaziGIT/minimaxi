<?php

namespace App\Repository;

use App\Entity\DiscountCode;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DiscountCode>
 */
class DiscountCodeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DiscountCode::class);
    }

    public function findLatestDiscountCode(): array
    {
        return $this->createQueryBuilder('d')
            ->where('d.validUntil > :today')
            ->setParameter('today', new \DateTime())
            ->orderBy('d.validUntil', 'ASC')
            ->setMaxResults(3)
            ->getQuery()
            ->getResult();
    }

    public function findAllOptimized()
    {
        return $this->createQueryBuilder('d')
            ->select('d', 'o')
            ->leftJoin('d.orders', 'o')
            ->getQuery()
            ->getResult();
    }
}
