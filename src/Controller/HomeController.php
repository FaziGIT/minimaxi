<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Repository\WishlistRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(ProductRepository $productRepository): Response
    {
        $products = $productRepository->findMostRecentProducts(4);
        $topProducts = $productRepository->findTopRatedProducts(4);
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'products' => $products,
            'topProducts' => $topProducts,
        ]);
    }

    #[Route('/products/new-arrivals', name: 'app_show_new_arrivals')]
    public function showAll(ProductRepository $productRepository): Response
    {
        $products = $productRepository->findMostRecentProducts(15);
        return $this->render('product/show_new_arrivals.html.twig', [
            'products' => $products,
        ]);
    }

    #[Route('/wishlist', name: 'wishlist')]
    public function wishlist(Request $request, WishlistRepository $wishlistRepository): Response
    {
        $user = $this->getUser();
        if (!$user instanceof Client) {
            throw $this->createAccessDeniedException('Vous devez être connecté en tant que client.');
        }

        $limit = 6; // Items per page
        $page = max(1, (int) $request->query->get('page', 1));
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
