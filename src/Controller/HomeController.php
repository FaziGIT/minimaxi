<?php

namespace App\Controller;

use App\Entity\Client;
use App\Repository\ProductRepository;
use App\Repository\WishlistRepository;
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

        // Nombre d'articles par page
        $limit = 4;
        $page = max(1, (int)$request->query->get('page', 1));
        $offset = ($page - 1) * $limit;

        $products = $wishlistRepository->findProductsByClientWithPagination($user, $limit, $offset);

        $totalProducts = $wishlistRepository->countProductsByClient($user);


        return $this->render('wishlist/index.html.twig', [
            'products' => $products,
            'currentPage' => $page,
            'totalPages' => ceil($totalProducts / $limit),
        ]);
    }


}
