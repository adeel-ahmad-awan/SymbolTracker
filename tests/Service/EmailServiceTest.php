<?php

namespace App\Tests\Service;

use App\Service\EmailService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class EmailServiceTest extends TestCase
{
    public function testSendEmail(): void
    {
        $mailerMock = $this->createMock(MailerInterface::class);
        $emailService = new EmailService($mailerMock);

        // Set up the expected values
        $emailAddress = 'sender@email.com';
        $subject = 'Test Subject';
        $body = 'test email body, test email body, test email body';

        // Set up the expected Email object
        $expectedEmail = (new Email())
            ->from(EmailService::FROM_EMAIL_ADDRESS)
            ->to($emailAddress)
            ->subject($subject)
            ->text($body);

        $mailerMock->expects($this->once())
            ->method('send')
            ->with($this->equalTo($expectedEmail));

        $emailService->sendEmail($emailAddress, $subject, $body);
    }
}