<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use App\Enum\SizeProductEnum;
use App\Form\ProductType;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Service\createImgFromProductForm;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/product')]
final class ProductController extends AbstractController
{
    public function __construct(private createImgFromProductForm $createImgFromProductForm)
    {
    }

    #[Route(name: 'app_admin_product', methods: ['GET'])]
    public function index(ProductRepository $productRepository): Response
    {
        return $this->render('product/adminIndex.html.twig', [
            'products' => $productRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_admin_newproduct')]
    public function newProduct(Request $request, EntityManagerInterface $entityManager, CategoryRepository $categoryRepository): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // recuperer l'image
            $this->createImgFromProductForm->createImage($product, $form, $entityManager);

            $entityManager->persist($product);
            $entityManager->flush();

            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('product/new.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_editproduct', methods: ['GET', 'POST'])]
    public function edit(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        // Cloner la collection d'images existantes
        $originalImages = new ArrayCollection();
        foreach ($product->getImageProducts() as $image) {
            $originalImages->add($image);
        }

        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
//          TODO: Faire un check ici qui regarde si les images sont encore la + recuperer la variable avec le dernier nombre dimages crees, puis refaire un fori ? Jsp go voir MatTonDeal
            $this->createImgFromProductForm->createImage($product, $form, $entityManager, $originalImages);

            $entityManager->flush();

            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('product/edit.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_product_delete', methods: ['POST'])]
    public function delete(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $product->getId(), $request->getPayload()->getString('_token'))) {
            // delete all the images
            foreach ($product->getImageProducts() as $image) {
//                \dd($image);
                if ($image->getUrl() && file_exists($this->getParameter('imagesProductDestination') . '/' . $image->getUrl())) {
                    unlink($this->getParameter('imagesProductDestination') . '/' . $image->getUrl());
                    $entityManager->remove($image);
                }
            }
            $entityManager->remove($product);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_admin_product', [], Response::HTTP_SEE_OTHER);
    }
}
