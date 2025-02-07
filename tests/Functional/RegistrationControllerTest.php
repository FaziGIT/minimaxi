<?php

namespace App\Tests\Functional;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
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

        /** @var EntityManager $em */
        $this->userRepository = $container->get(UserRepository::class);
    }

    public function testRegister(): void
    {
        // Register a new user
        $this->client->request('GET', '/register');
        self::assertResponseIsSuccessful();
        self::assertPageTitleContains("S'inscrire");

        $this->client->submitForm('S\'inscrire', [
            'registration_form[email]' => 'me@example.com',
            'registration_form[plainPassword]' => 'password1!',
            'registration_form[address]' => '8 rue du test',
            'registration_form[agreeTerms]' => true,
        ]);

        self::assertResponseRedirects('/');

        $user = $this->userRepository->findOneByEmail('me@example.com');

        self::assertNotNull($user, 'L\'utilisateur avec l\'email me@example.com n\'a pas été trouvé');

        self::assertFalse($user->isVerified());
    }
}
