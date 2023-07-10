<?php

namespace App\Service;

use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;

/**
 * @class
 */
class EmailService
{
    /**
     *
     */
    const FROM_EMAIL_ADDRESS = 'noreply@email.com';

    /**
     * @var \Symfony\Component\Mailer\MailerInterface
     */
    private MailerInterface $mailer;

    /**
     * @param \Symfony\Component\Mailer\MailerInterface $mailer
     */
    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * @param $emailAddress
     * @param $subject
     * @param $body
     *
     * @return void
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface
     */
    public function sendEmail($emailAddress, $subject, $body)
    {
        $email = (new Email())
            ->from(self::FROM_EMAIL_ADDRESS)
            ->to($emailAddress)
            ->subject($subject)
            ->text($body);
        $this->mailer->send($email);

    }
}