<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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

}
