<?php

namespace App\Tests\Functional;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class LoginControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testLogin(): void
    {
        // Denied
        $this->client->request('GET', '/login');
        self::assertResponseIsSuccessful();

        $this->client->submitForm('Se connecter', [
            '_username' => 'admin@admin.fr',
            '_password' => 'adminFalse',
        ]);

        self::assertResponseRedirects('/login');
        $this->client->followRedirect();

        // Pop up with an error
        self::assertSelectorTextContains('[role="alert"]', 'Nom d\'utilisateur ou mot de passe incorrect.');

        // Denied
        $this->client->request('GET', '/login');
        self::assertResponseIsSuccessful();

        // Success
        $this->client->submitForm('Se connecter', [
            '_username' => 'admin@admin.fr',
            '_password' => 'admin',
        ]);

        self::assertResponseRedirects('/');
        $this->client->followRedirect();

        self::assertSelectorNotExists('.alert-danger');
        self::assertResponseIsSuccessful();
    }
}
