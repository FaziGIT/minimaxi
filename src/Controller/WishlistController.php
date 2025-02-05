<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\Product;
use App\Entity\Wishlist;
use App\Repository\WishlistRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class WishlistController extends AbstractController
{
    #[Route('/wishlist', name: 'wishlist')]
    public function wishlist(Request $request, WishlistRepository $wishlistRepository): Response
    {
        $user = $this->getUser();
        if (!$user instanceof Client) {
            throw $this->createAccessDeniedException('Vous devez être connecté en tant que client.');
        }

        $limit = 6; // Items per page
        $page = max(1, (int)$request->query->get('page', 1));
        $offset = ($page - 1) * $limit;

        [$wishlistItems, $totalProducts] = $wishlistRepository->findPaginatedByClient($user, $limit, $offset);

        // Process to get the minimum image URL for each product
        $products = array_map(function ($wishlistItem) {
            $product = $wishlistItem->getProduct();
            $images = $product->getImageProducts();

            return [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'price' => $product->getPrice(),
                'image' => $images->isEmpty() ? null : $images->first()->getUrl(), // Assuming the first image is primary
            ];
        }, $wishlistItems);

        return $this->render('wishlist/index.html.twig', [
            'products' => $products,
            'currentPage' => $page,
            'totalPages' => ceil($totalProducts / $limit),
        ]);
    }

    #[Route('/wishlist/add/{id}', name: 'wishlist_add', methods: ['GET'])]
    public function addToWishlist(Product $product, EntityManagerInterface $entityManager, Request $request): Response
    {
        $user = $this->getUser();
        if (!$user instanceof Client) {
            throw $this->createAccessDeniedException('Vous devez être connecté en tant que client.');
        }

        $referer = $request->headers->get('referer');

        $wishlistProducts = $user->getWishlists()->map(fn($wishlist) => $wishlist->getProduct());
        if ($wishlistProducts->contains($product)) {
            $this->addFlash('warning', 'Ce produit est déjà dans votre liste de souhaits.');
            if ($referer) {
                return $this->redirect($referer);
            }
            return $this->redirectToRoute('wishlist');
        }

        $wishlist = new Wishlist();
        $wishlist->setClient($user);
        $wishlist->setProduct($product);
        $wishlistItem = $user->addWishlist($wishlist);

        $entityManager->persist($wishlistItem);
        $entityManager->flush();

        $this->addFlash('success', 'Le produit a été ajouté à votre liste de souhaits.');
        if ($referer) {
            return $this->redirect($referer);
        }

        return $this->redirectToRoute('wishlist');
    }

    #[Route('/wishlist/{id}', name: 'wishlist_remove', methods: ['POST'])]
    public function removeFromWishlist(Request $request, Product $product, EntityManagerInterface $entityManager, WishlistRepository $wishlistRepository): Response
    {
        $user = $this->getUser();
        if (!$user instanceof Client) {
            throw $this->createAccessDeniedException('Vous devez être connecté en tant que client.');
        }

        $currentPage = $request->query->getInt('page', 1);
        if ($this->isCsrfTokenValid('remove_wishlist' . $product->getId(), $request->getPayload()->getString('_token'))) {
            $wishlistItem = $wishlistRepository->findOneBy([
                'client' => $user,
                'product' => $product
            ]);
            if ($wishlistItem) {
                $entityManager->remove($wishlistItem);
                $entityManager->flush();
            }
        }

        return $this->redirectToRoute('wishlist', ['page' => $currentPage]);
    }

}
