<?php

namespace App\Repository;

use App\Entity\Client;
use App\Entity\Order;
use App\Enum\OrderStatusEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends ServiceEntityRepository<Order>
 */
class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    public function findByStatuses(Client $user, array $statuses): array
    {
        return $this->createQueryBuilder('o')
            ->select('o', 'oi', 'p')
            ->where('o.client = :user')
            ->leftJoin('o.orderItems', 'oi')
            ->leftJoin('oi.product', 'p')
            ->andWhere('o.status IN (:statuses)')
            ->setParameter('user', $user)
            ->setParameter('statuses', $statuses)
            ->orderBy('o.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByStatus(Client $user, string $status): array
    {
        return $this->createQueryBuilder('o')
            ->select('o', 'oi', 'p')
            ->where('o.client = :user')
            ->leftJoin('o.orderItems', 'oi')
            ->leftJoin('oi.product', 'p')
            ->andWhere('o.status = :status')
            ->setParameter('user', $user)
            ->setParameter('status', $status)
            ->orderBy('o.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findPaginatedByStatuses(Client $user, array $statuses, int $page, int $limit): array
    {
        $qb = $this->createQueryBuilder('o')
            ->where('o.client = :user')
            ->andWhere('o.status IN (:statuses)')
            ->setParameter('user', $user)
            ->setParameter('statuses', $statuses)
            ->orderBy('o.createdAt', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        $paginator = new Paginator($qb->getQuery());

        return [
            iterator_to_array($paginator),
            $paginator->count(),
        ];
    }

    public function findPaginatedByStatus(Client $user, string $status, int $page, int $limit): array
    {
        $qb = $this->createQueryBuilder('o')
            ->where('o.client = :user')
            ->andWhere('o.status = :status')
            ->setParameter('user', $user)
            ->setParameter('status', $status)
            ->orderBy('o.createdAt', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        $paginator = new Paginator($qb->getQuery());

        return [
            iterator_to_array($paginator),
            $paginator->count(),
        ];
    }




//    /**
//     * @return Order[] Returns an array of Order objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('o')
//            ->andWhere('o.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('o.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Order
//    {
//        return $this->createQueryBuilder('o')
//            ->andWhere('o.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
    /**
     * Fonction pour trouver un panier en cours pour l'utilisateur connecté
     * @param UserInterface $user
     * @return Order|null
     */
    public function findPendingOrderById(UserInterface $user): ?Order
    {
        return $this->createQueryBuilder('o')
            ->select('o', 'oi', 'p', 'ip')
            ->innerJoin('o.orderItems', 'oi')
            ->innerJoin('oi.product', 'p')
            ->leftJoin('p.imageProducts', 'ip')
            ->andWhere('o.client = :user')
            ->andWhere('o.status = :status')
            ->setParameter('user', $user)
            ->setParameter('status', OrderStatusEnum::PENDING)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
