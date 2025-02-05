<?php

namespace App\Controller\Admin;

use App\Entity\Client;
use App\Repository\ClientRepository;
use App\Repository\ReviewRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'app_admin')]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    #[Route('/show-users', name: 'app_admin_show_users')]
    public function showUsers(ClientRepository $clientRepository): Response
    {
        $clients = array_filter($clientRepository->findAllClient(), function ($client) {
            return !in_array('ROLE_ADMIN', $client->getRoles());
        });

        return $this->render('admin/show-users.html.twig', [
            'clients' => $clients,
        ]);
    }

    #[Route('/show-users-reviews/{id}', name: 'app_admin_show_users_review')]
    public function showUsersReview(Client $client, ReviewRepository $reviewRepository): Response
    {
        $reviewByUser = $reviewRepository->findAllReviewsByUser($client);

        return $this->render('admin/show-users-review.html.twig', [
            'reviews' => $reviewByUser,
            'client' => $client,
        ]);
    }

    #[Route('/show-users-reviews/{id}/ban', name: 'app_admin_ban_user')]
    public function handleBannedUser(Client $client, EntityManagerInterface $entityManager): Response
    {
        if (in_array('ROLE_BANNED', $client->getRoles())) {
            $client->setRoles(['ROLE_USER']);
        } else {
            $client->setRoles(['ROLE_BANNED']);
        }

        $entityManager->persist($client);
        $entityManager->flush();

        return $this->redirectToRoute('app_admin_show_users');
    }
}
