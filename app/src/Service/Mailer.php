<?php

namespace App\Service;

use Psr\Log\LoggerAwareTrait;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class Mailer
{
    use LoggerAwareTrait;

    public function __construct(private readonly MailerInterface $mailer) {}

    public function sendTemplatedEmail(string $email, string $subject, string $template, ?array $parameters = []): bool
    {
        $email = (new TemplatedEmail())
            ->to(new Address($email))
            ->subject($subject)
            ->htmlTemplate($template)
            ->context($parameters);

        try {
            $this->mailer->send($email);
            return true;
        } catch (TransportExceptionInterface $e) {
            $this->logger->error($e->getMessage());
            return false;
        }
    }
}
