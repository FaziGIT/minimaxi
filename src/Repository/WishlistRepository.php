<?php

namespace App\Repository;

use App\Entity\Client;
use App\Entity\Wishlist;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Wishlist>
 */
class WishlistRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Wishlist::class);
    }


    public function findProductsByClientWithPagination(Client $client, int $limit, int $offset): array
    {
        return $this->createQueryBuilder('w')
            ->innerJoin('w.product', 'p')
            ->leftJoin('p.imageProducts', 'img')
            ->select('p.id, p.name, p.price, MIN(img.url) AS image') // Récupère uniquement l'image principale
            ->where('w.client = :client')
            ->setParameter('client', $client)
            ->groupBy('p.id') // Évite les doublons
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getArrayResult();
    }


    public function countProductsByClient(Client $client): int
    {
        return (int) $this->createQueryBuilder('w')
            ->select('COUNT(DISTINCT w.product)') // Compter les produits uniques
            ->where('w.client = :client')
            ->setParameter('client', $client)
            ->getQuery()
            ->getSingleScalarResult();
    }


    //    /**
    //     * @return Wishlist[] Returns an array of Wishlist objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('w')
    //            ->andWhere('w.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('w.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Wishlist
    //    {
    //        return $this->createQueryBuilder('w')
    //            ->andWhere('w.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
