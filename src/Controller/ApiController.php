<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\Product;
use App\Entity\Wishlist;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api')]
class ApiController extends AbstractController
{
    #[Route('/wishlist/add/{id}', name: 'api_wishlist_add')]
    public function index(Product $product, EntityManagerInterface $entityManager, Request $request): Response
    {
        $user = $this->getUser();
        if (!$user instanceof Client) {
            return $this->json(['type' => 'error', 'message' => 'Vous devez être connecté en tant que client.']);
        }

        $wishlistProducts = $user->getWishlists()->map(fn($wishlist) => $wishlist->getProduct());
        if ($wishlistProducts->contains($product)) {
            return $this->json(['type' => 'warning', 'message' => 'Ce produit est déjà dans votre liste de souhaits.']);
        }

        $wishlist = new Wishlist();
        $wishlist->setClient($user);
        $wishlist->setProduct($product);
        $wishlistItem = $user->addWishlist($wishlist);

        $entityManager->persist($wishlistItem);
        $entityManager->flush();

        return $this->json(['type' => 'success', 'message' => 'Le produit a été ajouté à votre liste de souhaits.']);
    }
}
