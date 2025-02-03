<?php

namespace App\Controller;

use App\Entity\OrderItem;
use App\Enum\OrderStatusEnum;
use App\Repository\DiscountCodeRepository;
use App\Repository\OrderRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class OrderController extends AbstractController
{
    #[Route('/cart', name: 'app_order_index', methods: ['GET'])]
    public function index(OrderRepository $orderRepository): Response
    {
        $cart = $orderRepository->findPendingOrderById($this->getUser());

        // Call the Voter
        if (!$this->isGranted('view', $cart)) {
            return $this->redirectToRoute('app_home');
        }

        return $this->render('order/index.html.twig', [
            'cart' => $cart,
        ]);
    }

//    Passer le Voter pour valider la validite du panier
    #[Route('/cart/validate', name: 'app_order_validate', methods: ['GET'])]
    public function validateCart(OrderRepository $orderRepository, EntityManagerInterface $entityManager): Response
    {
        $cart = $orderRepository->findPendingOrderById($this->getUser());

        // Call the Voter
        if (!$this->isGranted('validate', $cart)) {
            return $this->redirectToRoute('app_order_index', [], Response::HTTP_SEE_OTHER);
        }

        $cart->setStatus(OrderStatusEnum::PAID);
        $cart->setUpdatedAt(new DateTimeImmutable());

        $entityManager->flush();
        $this->addFlash('success', 'Votre commande a été payée avec succès');
        return $this->redirectToRoute('app_order_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/cart/remove/{id}', name: 'app_order_remove_item', methods: ['GET'])]
    public function deleteOrderItemFromCart(OrderItem $orderItem, EntityManagerInterface $entityManager): Response
    {
        if ($orderItem->getLinkedOrder()->getClient() !== $this->getUser()) {
            return $this->redirectToRoute('app_order_index', [], Response::HTTP_SEE_OTHER);
        }

        $entityManager->remove($orderItem);
        $entityManager->flush();
        return $this->redirectToRoute('app_order_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/cart/update/{id}', name: 'app_order_update_item', methods: ['POST'])]
    public function updateQuantityOrderItem(OrderItem $orderItem, Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($orderItem->getLinkedOrder()->getClient() !== $this->getUser()) {
            return $this->redirectToRoute('app_order_index', [], Response::HTTP_SEE_OTHER);
        }

        $order = $orderItem->getLinkedOrder();
        $action = $request->request->get('action');

        if ($action === 'increase') {
            // regarder si il ya assez en stock
            $product = $orderItem->getProduct();
            if ($product->getStockQuantity() < $orderItem->getQuantity() + 1) {
                $this->addFlash('error', 'Stock insuffisant pour l\'article ' . $product->getName());
                return $this->redirectToRoute('app_order_index', [], Response::HTTP_SEE_OTHER);
            }
            $orderItem->setQuantity($orderItem->getQuantity() + 1);
        } elseif ($action === 'decrease' && $orderItem->getQuantity() > 1) {
            $orderItem->setQuantity($orderItem->getQuantity() - 1);
        } elseif ($action === 'decrease' && $orderItem->getQuantity() === 1) {
            return $this->redirectToRoute('app_order_remove_item', ['id' => $orderItem->getId()], Response::HTTP_SEE_OTHER);
        }

        // update global price
        $totalPrice = 0;
        foreach ($order->getOrderItems() as $item) {
            $item->setGlobalPrice($item->getProduct()->getPrice() * $item->getQuantity());
            $totalPrice += $item->getGlobalPrice();
        }

        // check if discount code is applied
        if ($order->getAppliedDiscount()) {
            $totalPrice = $totalPrice - ($totalPrice * $order->getAppliedDiscount()->getPercentage() / 100);
        }

        $order->setTotalPrice($totalPrice);
        $entityManager->flush();
        return $this->redirectToRoute('app_order_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/cart/apply-discount', name: 'app_order_apply_discount', methods: ['POST'])]
    public function appliedDiscountCode(OrderRepository $orderRepository, DiscountCodeRepository $discountCodeRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        $cart = $orderRepository->findPendingOrderById($this->getUser());
        $discountCode = strtoupper($request->request->get('discountCode'));
        $verifiedDiscountCode = $discountCodeRepository->findOneBy(['code' => $discountCode]);
        $totalGlobalPrice = 0;

        if (!$verifiedDiscountCode) {
            $this->addFlash('errorDiscountCode', 'Code de réduction invalide');
            return $this->redirectToRoute('app_order_index', [], Response::HTTP_SEE_OTHER);
        } elseif ($verifiedDiscountCode->getValidUntil() < new DateTimeImmutable()) {
            $this->addFlash('errorDiscountCode', 'Code de réduction expiré');
            return $this->redirectToRoute('app_order_index', [], Response::HTTP_SEE_OTHER);
        } elseif ($cart->getAppliedDiscount()) {
            foreach ($cart->getOrderItems() as $item) {
                $totalGlobalPrice += $item->getGlobalPrice();
            }
            $cart->setTotalPrice($totalGlobalPrice);
        }

        $cart->setAppliedDiscount($verifiedDiscountCode);
        $percentageDiscountCode = $verifiedDiscountCode->getPercentage();
        $cart->setTotalPrice($cart->getTotalPrice() - ($cart->getTotalPrice() * $percentageDiscountCode / 100));

        $entityManager->flush();

        return $this->redirectToRoute('app_order_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/cart/remove-discount', name: 'app_order_remove_discount', methods: ['GET'])]
    public function deleteDiscountCode(OrderRepository $orderRepository, EntityManagerInterface $entityManager): Response
    {
        $cart = $orderRepository->findPendingOrderById($this->getUser());
        if (!$cart->getAppliedDiscount()) {
            return $this->redirectToRoute('app_order_index', [], Response::HTTP_SEE_OTHER);
        }
        $cart->setAppliedDiscount(null);

        // update global price
        $totalPrice = 0;
        foreach ($cart->getOrderItems() as $item) {
            $item->setGlobalPrice($item->getProduct()->getPrice() * $item->getQuantity());
            $totalPrice += $item->getGlobalPrice();
        }
        $cart->setTotalPrice($totalPrice);
        $entityManager->flush();

        return $this->redirectToRoute('app_order_index', [], Response::HTTP_SEE_OTHER);
    }

}
