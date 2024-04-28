<?php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class EmailService
{
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendEmail(string $recipient, string $subject, string $body): void
    {
        $email = (new Email())
            ->from('gaidi.oumaiima54@gmail.com') // Change this to your email address
            ->to($recipient)
            ->subject($subject)
            ->text($body);

        $this->mailer->send($email);
    }
}
