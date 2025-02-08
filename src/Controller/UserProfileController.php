<?php

namespace App\Controller;

use App\Entity\Client;
use App\Enum\OrderStatusEnum;
use App\Form\ClientEditType;
use App\Repository\DiscountCodeRepository;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/profile')]
class UserProfileController extends AbstractController
{
    #[Route('/', name: 'app_user_profile')]
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

        $currentOrders = $orderRepository->findByStatuses($user, $currentStatuses);
        $deliveredOrders = $orderRepository->findByStatus($user, OrderStatusEnum::DELIVERED->value);

        return $this->render('user_profile/index.html.twig', [
            'user' => $user,
            'currentOrders' => $currentOrders,
            'deliveredOrders' => $deliveredOrders,
            'discountCodes' => $discountCodes,
        ]);
    }

    #[Route('/orders/{type}', name: 'app_user_orders')]
    public function viewAllOrders(string $type, OrderRepository $orderRepository, Request $request): Response
    {
        $user = $this->getUser();
        if (!$user instanceof Client) {
            throw $this->createAccessDeniedException('Vous devez être connecté.');
        }

        $page = max(1, (int)$request->query->get('page', 1));
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

        $totalPages = (int)ceil($totalOrders / $limit);

        return $this->render('user_profile/orders.html.twig', [
            'orders' => $orders,
            'type' => $type,
            'currentPage' => $page,
            'totalPages' => $totalPages,
        ]);
    }


    #[Route('/edit', name: 'app_profile_edit')]
    public function editProfile(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if (!$user instanceof Client) {
            throw $this->createAccessDeniedException('Vous devez être connecté en tant que client.');
        }

        $form = $this->createForm(ClientEditType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('success', 'Vos informations ont été mises à jour.');

                return $this->redirectToRoute('app_profile_edit');
            } else {
                $this->addFlash('error', 'Une erreur est survenue. Veuillez vérifier vos informations.');
            }
        }

        return $this->render('user_profile/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
