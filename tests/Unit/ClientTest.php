<?php

namespace App\Tests\Unit;

use App\Entity\Client;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\ConstraintViolation;

class ClientTest extends KernelTestCase
{
    private mixed $validator;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->validator = self::getContainer()->get('validator');
    }

    public function getEntity(): Client
    {
        return (new Client())
            ->setEmail('admin@admin.fr')
            ->setPassword('$2y$13$gaFbir.8s7Th6/WTCAp75.KUi/RTg2friW3LavolYwIbu90pYmgyO') // password
            ->setAvatar('')
            ->setCreatedAt(new \DateTime())
            ->setVerified(true)
            ->setAddress('8 rue du test')
            ->setPhoneNumber('0000000000')
            ->setDiscountPoint(0);
    }

    public function assertHasErrors(Client $code, int $number = 0): void
    {
        $errors = $this->validator->validate($code);
        $messages = [];
        /** @var ConstraintViolation $error */
        foreach ($errors as $error) {
            $messages[] = $error->getPropertyPath() . ' => ' . $error->getMessage();
        }
        $this->assertCount($number, $errors, implode(', ', $messages));
    }

    public function testValidEntity(): void
    {
        $this->assertHasErrors($this->getEntity());
    }

    public function testInvalidEntityEmail(): void
    {
        $this->assertHasErrors($this->getEntity()->setEmail(''), 1);
    }

    public function testValidEntityWithNullPhoneNumber(): void
    {
        $this->assertHasErrors($this->getEntity()->setPhoneNumber(''));
    }
}
