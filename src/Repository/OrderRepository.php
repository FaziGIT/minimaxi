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

    public function findAllByUser(Client $user): array
    {
        return $this->createQueryBuilder('o')
            ->select('o', 'oi', 'p')
            ->where('o.client = :user')
            ->leftJoin('o.orderItems', 'oi')
            ->leftJoin('oi.product', 'p')
            ->setParameter('user', $user)
            ->orderBy('o.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findPaginatedOrders(Client $user, array|string $statuses, int $page, int $limit): array
    {
        $qb = $this->createQueryBuilder('o')
            ->select('o', 'oi', 'p')
            ->leftJoin('o.orderItems', 'oi')
            ->leftJoin('oi.product', 'p')
            ->where('o.client = :user')
            ->setParameter('user', $user)
            ->orderBy('o.createdAt', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);
        if (is_array($statuses)) {
            $qb->andWhere('o.status IN (:statuses)')
                ->setParameter('statuses', $statuses);
        } else {
            $qb->andWhere('o.status = :status')
                ->setParameter('status', $statuses);
        }

        $paginator = new Paginator($qb->getQuery());

        return [
            iterator_to_array($paginator),
            $paginator->count(),
        ];
    }

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
