<?php

namespace App\Repository;

use App\Entity\Client;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Client>
 */
class ClientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Client::class);
    }

    /**
     * @return array<Client>
     */
    public function findAllClient(): array
    {
        return $this->createQueryBuilder('c')
            ->select('c', 'r')
            ->leftJoin('c.reviews', 'r')
            ->getQuery()
            ->getResult();
    }
}
