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

    /**
     * @param Client $client
     * @param int $limit
     * @param int $offset
     * @return array<int, array<int, Wishlist>|int>
     */
    public function findPaginatedByClient(Client $client, int $limit, int $offset): array
    {
        $qb = $this->createQueryBuilder('w')
            ->innerJoin('w.product', 'p')
            ->leftJoin('p.imageProducts', 'img')
            ->addSelect('p', 'img')
            ->where('w.client = :client')
            ->setParameter('client', $client)
            ->orderBy('p.name', 'ASC')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        $query = $qb->getQuery();

        $paginator = new Paginator($query);

        return [
            iterator_to_array($paginator),
            $paginator->count(),
        ];
    }
}
