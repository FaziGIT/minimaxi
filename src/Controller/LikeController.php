<?php

namespace App\Controller;

use App\Entity\Like;
use App\Entity\Review;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/like')]
final class LikeController extends AbstractController
{
    #[Route('/add/{id}', name: 'app_like_index', methods: ['POST'])]
    public function handleLike(Review $review, EntityManagerInterface $entityManager): Response
    {
        $like = new Like();

        $like->setReview($review);
        $like->setClient($this->getUser());
        $like->setCreatedAt(new \DateTimeImmutable());

        $entityManager->persist($like);
        $entityManager->flush();

        return $this->redirectToRoute('app_product_show', ['id' => $review->getProduct()->getId()], Response::HTTP_SEE_OTHER);
    }

    #[Route('/remove/{id}', name: 'app_unlike_index', methods: ['POST'])]
    public function handleRemoveLike(Review $review, EntityManagerInterface $entityManager): Response
    {
        $like = $entityManager->getRepository(Like::class)->findOneBy([
            'review' => $review,
            'client' => $this->getUser()
        ]);

        $entityManager->remove($like);
        $entityManager->flush();

        return $this->redirectToRoute('app_product_show', ['id' => $review->getProduct()->getId()], Response::HTTP_SEE_OTHER);
    }
}
