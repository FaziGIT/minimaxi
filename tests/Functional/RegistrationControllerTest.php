<?php

namespace App\Tests\Functional;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RegistrationControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $container = static::getContainer();

        // On utilise le service UserRepositoryTest du conteneur
        $this->userRepository = $container->get(UserRepository::class);
    }

    public function testRegister(): void
    {
        // Envoie une requête GET à la page d'inscription
        $this->client->request('GET', '/register');
        self::assertResponseIsSuccessful();
        self::assertPageTitleContains("S'inscrire");

        // Soumet le formulaire d'inscription
        $this->client->submitForm('S\'inscrire', [
            'registration_form[email]' => 'me@example.com',
            'registration_form[plainPassword]' => 'password1!',
            'registration_form[address]' => '8 rue du test',
            'registration_form[agreeTerms]' => true,
        ]);

        // Vérifie la redirection après l'inscription
        self::assertResponseRedirects('/');

        // Vérifie que l'utilisateur a bien été enregistré
        $user = $this->userRepository->findOneBy(['email' => 'me@example.com']);
        self::assertNotNull($user, 'L\'utilisateur avec l\'email me@example.com n\'a pas été trouvé');
        self::assertFalse($user->isVerified());
    }
}
