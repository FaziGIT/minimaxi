<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Review;
use App\Form\ReviewType;
use App\Repository\ProductRepository;
use App\Repository\ReviewRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/product')]
final class ProductController extends AbstractController
{
    #[Route(name: 'app_product_index', methods: ['GET'])]
    public function index(ProductRepository $productRepository): Response
    {
        $products = $productRepository->findAllOptimized();

        $productsArray = array_map(function ($product) {
            return [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'description' => $product->getDescription(),
                'price' => $product->getPrice(),
                'stockQuantity' => $product->getStockQuantity(),
                'createdAt' => $product->getCreatedAt()?->format('Y-m-d H:i:s'),
                'category' => $product->getCategory()?->getName(),
                'size' => $product->getSize()?->value
            ];
        }, $products);

        return $this->render('product/index.html.twig', [
            'products' => json_encode($productsArray)
        ]);
    }

    #[Route('/{id}', name: 'app_product_show', methods: ['GET', 'POST'])]
    public function show(Product $product, Request $request, EntityManagerInterface $entityManager, ReviewRepository $reviewRepository): Response
    {

        $review = new Review();
        $form = $this->createForm(ReviewType::class, $review);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($review->getRating() > 5) {
                $review->setRating(5);
            }

            $review->setClient($this->getUser());
            $review->setProduct($product);
            $review->setUpdatedAt(new \DateTimeImmutable());
            $review->setCreatedAt(new \DateTimeImmutable());

            $entityManager->persist($review);
            $entityManager->flush();

            return $this->redirectToRoute('app_product_show', ['id' => $product->getId()], Response::HTTP_SEE_OTHER);
        }

        $getReviewsFromProduct = $reviewRepository->getReviewsFromProduct($product);

        $nonBannedReviews = array_filter($getReviewsFromProduct, function ($review) {
            return !in_array('ROLE_BANNED', $review->getClient()->GetRoles());
        });

        return $this->render('product/show.html.twig', [
            'product' => $product,
            'reviews' => $nonBannedReviews,
            'form' => $form->createView()
        ]);
    }
}
