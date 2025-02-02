<?php

namespace App\Controller;

use App\Entity\Client;
use App\Enum\OrderStatusEnum;
use App\Repository\DiscountCodeRepository;
use App\Repository\OrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
}
