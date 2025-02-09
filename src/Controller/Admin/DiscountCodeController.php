<?php

namespace App\Controller\Admin;

use App\Entity\DiscountCode;
use App\Form\DiscountCodeType;
use App\Repository\DiscountCodeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/discount-code')]
final class DiscountCodeController extends AbstractController
{
    #[Route(name: 'app_discount_code_index', methods: ['GET'])]
    public function index(DiscountCodeRepository $discountCodeRepository): Response
    {
        return $this->render('discount_code/index.html.twig', [
            'discount_codes' => $discountCodeRepository->findAllOptimized(),
        ]);
    }

    #[Route('/new', name: 'app_discount_code_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $discountCode = new DiscountCode();
        $form = $this->createForm(DiscountCodeType::class, $discountCode);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $uppercase = \strtoupper($discountCode->getCode());
            $discountCode->setCode($uppercase);

            if ($discountCode->getPercentage() > 100) {
                $discountCode->setPercentage(100);
            }

            $entityManager->persist($discountCode);
            $entityManager->flush();

            return $this->redirectToRoute('app_discount_code_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('discount_code/new.html.twig', [
            'discount_code' => $discountCode,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_discount_code_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, DiscountCode $discountCode, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(DiscountCodeType::class, $discountCode);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($discountCode->getPercentage() > 100) {
                $discountCode->setPercentage(100);
            }

            // update the global price
            foreach ($discountCode->getOrders() as $order) {
                $totalPrice = 0;
                foreach ($order->getOrderItems() as $item) {
                    $item->setGlobalPrice($item->getProduct()->getPrice() * $item->getQuantity());
                    $totalPrice += $item->getGlobalPrice();
                }

                // add the discount
                $discount = $totalPrice * $discountCode->getPercentage() / 100;
                $order->setTotalPrice($totalPrice - $discount);
            }


            $entityManager->flush();

            return $this->redirectToRoute('app_discount_code_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('discount_code/edit.html.twig', [
            'discount_code' => $discountCode,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_discount_code_delete', methods: ['POST'])]
    public function delete(Request $request, DiscountCode $discountCode, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $discountCode->getId(), $request->getPayload()->getString('_token'))) {
            try {
                //delete all the orders linked to the discount code
                foreach ($discountCode->getOrders() as $order) {
                    $order->setAppliedDiscount(null);
                }

                // update the global price
                foreach ($discountCode->getOrders() as $order) {
                    $totalPrice = 0;
                    foreach ($order->getOrderItems() as $item) {
                        $item->setGlobalPrice($item->getProduct()->getPrice() * $item->getQuantity());
                        $totalPrice += $item->getGlobalPrice();
                    }
                    $order->setTotalPrice($totalPrice);
                }

                $entityManager->remove($discountCode);
                $entityManager->flush();

                $this->addFlash('success', 'Le code promo a été supprimé avec succès.');
            } catch (\Exception $e) {
                // Message d'erreur en cas d'échec
                $this->addFlash('error', 'Une erreur est survenue lors de la suppression du code promo.');
            }
        } else {
            $this->addFlash('error', 'La suppression du code promo a échoué.');
        }

        return $this->redirectToRoute('app_discount_code_index', [], Response::HTTP_SEE_OTHER);
    }
}
