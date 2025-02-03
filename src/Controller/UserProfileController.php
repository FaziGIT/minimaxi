<?php

namespace App\Controller;

use App\Entity\Client;
use App\Enum\OrderStatusEnum;
use App\Repository\DiscountCodeRepository;
use App\Repository\OrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class UserProfileController extends AbstractController
{
    #[Route('/profile', name: 'app_user_profile')]
    public function profile(OrderRepository $orderRepository, DiscountCodeRepository $discountCodeRepository): Response
    {
        $user = $this->getUser();
        if (!$user instanceof Client) {
            throw $this->createAccessDeniedException('Vous devez être connecté.');
        }

        $discountCodes = $discountCodeRepository->findLatestDiscountCode(3);

        $currentStatuses = [
            OrderStatusEnum::PAID->value,
            OrderStatusEnum::PENDING->value,
            OrderStatusEnum::SHIPPED->value,
        ];
        $currentOrders = $orderRepository->findByStatuses($user, $currentStatuses, 4);
        $deliveredOrders = $orderRepository->findByStatus($user, OrderStatusEnum::DELIVERED->value, 4);

        return $this->render('user_profile/index.html.twig', [
            'user' => $user,
            'currentOrders' => $currentOrders,
            'deliveredOrders' => $deliveredOrders,
            'discountCodes' => $discountCodes,
        ]);
    }

    #[Route('/profile/orders/{type}', name: 'app_user_orders')]
    public function viewAllOrders(string $type, OrderRepository $orderRepository, Request $request): Response
    {
        $user = $this->getUser();
        if (!$user instanceof Client) {
            throw $this->createAccessDeniedException('Vous devez être connecté.');
        }

        $page = max(1, (int) $request->query->get('page', 1));
        $limit = 6;

        $orders = [];
        $totalOrders = 0;

        if ($type === 'current') {
            $statuses = [
                OrderStatusEnum::PAID->value,
                OrderStatusEnum::PENDING->value,
                OrderStatusEnum::SHIPPED->value,
            ];
            [$orders, $totalOrders] = $orderRepository->findPaginatedByStatuses($user, $statuses, $page, $limit);
        } elseif ($type === 'delivered') {
            [$orders, $totalOrders] = $orderRepository->findPaginatedByStatus($user, OrderStatusEnum::DELIVERED->value, $page, $limit);
        }

        $totalPages = (int) ceil($totalOrders / $limit);

        return $this->render('user_profile/orders.html.twig', [
            'orders' => $orders,
            'type' => $type,
            'currentPage' => $page,
            'totalPages' => $totalPages,
        ]);
    }

}
