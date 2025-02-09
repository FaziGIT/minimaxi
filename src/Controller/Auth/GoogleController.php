<?php

namespace App\Controller\Auth;

use App\DTO\GoogleDTO;
use App\Entity\Client;
use App\Form\GoogleFormType;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class GoogleController extends AbstractController
{
    #[Route(path: '/connect/google', name: 'connect_google')]
    public function connectAction(ClientRegistry $clientRegistry): RedirectResponse
    {
        return $clientRegistry
            ->getClient('google')
            ->redirect(['profile'], []);
    }

    #[Route(path: '/connect/google/check', name: 'connect_google_check')]
    public function connectCheckAction(): JsonResponse|RedirectResponse
    {
        if (!$this->getUser()) {
            return new JsonResponse(array('status' => false, 'message' => "Utilisateur introuvable"));
        } else {
            return $this->redirectToRoute('app_home');
        }
    }

    #[Route(path: '/add-address', name: 'add_address')]
    public function addAddress(Request $request, EntityManagerInterface $entityManager): Response
    {
        /** @var Client $user */
        $user = $this->getUser();

        // if password is empty = google account
        if (!(empty($user->getPassword()) && empty($user->getAddress()))) {
            return $this->redirectToRoute('app_home');
        }

        $client = new GoogleDTO();
        $form = $this->createForm(GoogleFormType::class, $client);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user->setAddress($form->get('address')->getData());

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_home');
        }
        return $this->render('registration/add_address.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
