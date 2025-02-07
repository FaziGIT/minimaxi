<?php

namespace App\Repository;

use App\Entity\Client;
use App\Entity\Product;
use App\Entity\Wishlist;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
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

    public function findPaginatedByClient(Client $client, int $limit, int $offset): array
    {
        $qb = $this->createQueryBuilder('w')
            ->innerJoin('w.product', 'p')
            ->leftJoin('p.imageProducts', 'img')
            ->addSelect('p', 'img') // Fetch the full product and image entities
            ->where('w.client = :client')
            ->setParameter('client', $client)
            ->orderBy('p.name', 'ASC') // Sort by product name
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        $query = $qb->getQuery();

        // Use the Doctrine Paginator
        $paginator = new Paginator($query);

        return [
            iterator_to_array($paginator), // Paginated results
            $paginator->count(),          // Total number of results
        ];
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
