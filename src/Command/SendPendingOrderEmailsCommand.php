<?php

namespace App\Command;

use App\Entity\Order;
use App\Enum\OrderStatusEnum;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'app:send-pending-order-emails')]
class SendPendingOrderEmailsCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private MailerInterface $mailer;

    public function __construct(EntityManagerInterface $entityManager, MailerInterface $mailer)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->mailer = $mailer;
    }

    protected function configure(): void
    {
        $this->setDescription('Envoie un email à tous les utilisateurs ayant une commande en cours avec au mois un produit dedans.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $orderRepository = $this->entityManager->getRepository(Order::class);
        $orders = $orderRepository->findBy(['status' => OrderStatusEnum::PENDING]);

        $usersToNotify = [];
        foreach ($orders as $order) {
            if ($order->getOrderItems()->count() > 0) {
                $client = $order->getClient();
                if ($client && !in_array($client, $usersToNotify, true)) {
                    $usersToNotify[] = $client;
                }
            }
        }

        // Envoyer les emails par vagues de 5
        $chunkSize = 5;
        $chunks = array_chunk($usersToNotify, $chunkSize);

        foreach ($chunks as $chunk) {
            foreach ($chunk as $user) {
                try {
                    $email = (new TemplatedEmail())
                        ->from(new Address('no-reply@minimaxi.fr', 'MiniMaxi Website'))
                        ->to((string)$user->getEmail())
                        ->subject('Rappel : Votre commande est en attente')
                        ->htmlTemplate('emails/pending_order_email.html.twig');

                    $this->mailer->send($email);
                    $output->writeln('Email envoyé à : ' . $user->getEmail());
                } catch (TransportExceptionInterface $e) {
                    $output->writeln('Erreur lors de l\'envoi de l\'email à : ' . $user->getEmail());
                }
            }
            $output->writeln("Vague d'emails envoyée. Pausing before next batch.");
            sleep(5);
        }

        return Command::SUCCESS;
    }
}
