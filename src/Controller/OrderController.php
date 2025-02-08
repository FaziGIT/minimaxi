<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\Product;
use App\Enum\OrderStatusEnum;
use App\Repository\DiscountCodeRepository;
use App\Repository\OrderRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Exception\DatabaseDoesNotExist;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class OrderController extends AbstractController
{
    #[Route('/cart/add/{id}', name: 'app_order_add_to_cart', methods: ['GET'])]
    public function addToCart(Product $product, EntityManagerInterface $entityManager, OrderRepository $orderRepository, Request $request): Response
    {
        $cart = $orderRepository->findPendingOrderById($this->getUser());

        if (!$cart) {
            $cart = new Order();
            $cart->setClient($this->getUser());
            $cart->setStatus(OrderStatusEnum::PENDING);
            $entityManager->persist($cart);
        }

        // Vérifier si le produit est déjà dans le panier
        $existingOrderItem = null;
        foreach ($cart->getOrderItems() as $item) {
            if ($item->getProduct()->getId() === $product->getId()) {
                $existingOrderItem = $item;
                break;
            }
        }

        if ($existingOrderItem) {
            // Si le produit est déjà dans le panier, on augmente la quantité
            $existingOrderItem->setQuantity($existingOrderItem->getQuantity() + 1);
            $existingOrderItem->setGlobalPrice($existingOrderItem->getQuantity() * $product->getPrice());
        } else {
            // Sinon, on ajoute un nouvel article
            $orderItem = new OrderItem();
            $orderItem->setProduct($product);
            $orderItem->setQuantity(1);
            $orderItem->setGlobalPrice($product->getPrice());
            $orderItem->setLinkedOrder($cart);

            $cart->addOrderItem($orderItem);

            $entityManager->persist($orderItem);
        }
        $this->addFlash('success', 'Produit "' . $product->getName() . '" ajouté au panier.');

        $totalPrice = 0;
        foreach ($cart->getOrderItems() as $item) {
            $totalPrice += $item->getGlobalPrice();
        }

        if ($cart->getAppliedDiscount()) {
            $discountCode = $cart->getAppliedDiscount();
            $percentageDiscount = $discountCode->getPercentage();
            $totalPrice -= ($totalPrice * $percentageDiscount / 100);
        }

        $cart->setTotalPrice($totalPrice);

        $entityManager->persist($cart);
        $entityManager->flush();

        $referer = $request->headers->get('referer');
        if ($referer) {
            return $this->redirect($referer);
        }

        return $this->redirectToRoute('app_order_index');
    }

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

        $error = false;
        $itemsToRemove = [];
        foreach ($cart->getOrderItems() as $item) {
            $product = $item->getProduct();
            $stockQuantity = $product->getStockQuantity();
            $requestedQuantity = $item->getQuantity();

            if ($stockQuantity < $requestedQuantity) {
                $error = true;
                if ($stockQuantity > 0) {
                    $item->setQuantity($stockQuantity);
                    $item->setGlobalPrice($product->getPrice() * $stockQuantity);
                    $this->addFlash('error', 'Stock insuffisant pour l\'article ' . $product->getName() . '. Quantité ajustée à ' . $stockQuantity);
                } else {
                    // If no stock available, remove the item from the cart
                    $itemsToRemove[] = $item;
                    $this->addFlash('error', 'Article ' . $product->getName() . ' retiré du panier car plus de stock disponible');
                }
            }
        }

        foreach ($itemsToRemove as $item) {
            $cart->removeOrderItem($item);
            $entityManager->remove($item);
        }

        if ($error) {
            $totalPrice = 0;
            foreach ($cart->getOrderItems() as $item) {
                $totalPrice += $item->getGlobalPrice();
            }

            if ($cart->getAppliedDiscount()) {
                $discountCode = $cart->getAppliedDiscount();
                $percentageDiscount = $discountCode->getPercentage(); // Assurez-vous que cette méthode existe
                $totalPrice = $totalPrice - ($totalPrice * $percentageDiscount / 100);
            }

            $cart->setTotalPrice($totalPrice);

            $entityManager->flush();
            return $this->redirectToRoute('app_order_index', [], Response::HTTP_SEE_OTHER);
        }

        $totalPrice = 0;
        foreach ($cart->getOrderItems() as $item) {
            $product = $item->getProduct();
            $product->setStockQuantity($product->getStockQuantity() - $item->getQuantity());

            // Mise à jour du prix global de l'item
            $item->setGlobalPrice($item->getProduct()->getPrice() * $item->getQuantity());
            $totalPrice += $item->getGlobalPrice();
        }

        if ($cart->getAppliedDiscount()) {
            $discountCode = $cart->getAppliedDiscount();
            $percentageDiscount = $discountCode->getPercentage();
            $totalPrice = $totalPrice - ($totalPrice * $percentageDiscount / 100);
        }

        $cart->setTotalPrice($totalPrice);
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

        // update global price
        $order = $orderItem->getLinkedOrder();
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

        $entityManager->remove($orderItem);
        $entityManager->flush();
        return $this->redirectToRoute('app_order_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/cart/update/{id}', name: 'app_order_update_item', methods: ['POST'])]
    public function updateQuantityOrderItem(OrderItem $orderItem, Request $request, EntityManagerInterface $entityManager): Response
    {
        $order = $orderItem->getLinkedOrder();

        // Vérifie que l'utilisateur est bien le propriétaire de la commande
        if ($order->getClient() !== $this->getUser()) {
            return $this->redirectToRoute('app_order_index', [], Response::HTTP_SEE_OTHER);
        }

        $action = $request->request->get('action');

        if ($action === 'increase') {
            // Vérifie le stock disponible avant d'ajouter une unité
            $product = $orderItem->getProduct();
            if ($product->getStockQuantity() < $orderItem->getQuantity() + 1) {
                $this->addFlash('error', 'Stock insuffisant pour l\'article ' . $product->getName());
                return $this->redirectToRoute('app_order_index', [], Response::HTTP_SEE_OTHER);
            }
            $orderItem->setQuantity($orderItem->getQuantity() + 1);
        } elseif ($action === 'decrease' && $orderItem->getQuantity() > 1) {
            $orderItem->setQuantity($orderItem->getQuantity() - 1);
        } elseif ($action === 'decrease' && $orderItem->getQuantity() === 1) {
            $order->removeOrderItem($orderItem);
            $entityManager->remove($orderItem);
        }

        $totalPrice = 0;
        foreach ($order->getOrderItems() as $item) {
            $item->setGlobalPrice($item->getProduct()->getPrice() * $item->getQuantity());
            $totalPrice += $item->getGlobalPrice();
        }

        if ($order->getAppliedDiscount()) {
            $totalPrice -= ($totalPrice * $order->getAppliedDiscount()->getPercentage() / 100);
        }

        $order->setTotalPrice($totalPrice);

        $entityManager->persist($order);
        $entityManager->flush();

        return $this->redirectToRoute('app_order_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/cart/apply-discount', name: 'app_order_apply_discount', methods: ['POST'])]
    public function appliedDiscountCode(OrderRepository $orderRepository, DiscountCodeRepository $discountCodeRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        $cart = $orderRepository->findPendingOrderById($this->getUser());
        $discountCode = \trim(strtoupper($request->request->get('discountCode')));
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
