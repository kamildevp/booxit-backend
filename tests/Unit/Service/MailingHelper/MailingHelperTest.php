<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\MailingHelper;

use App\Exceptions\MailingHelperException;
use App\Service\MailingHelper\MailingHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;

class MailingHelperTest extends TestCase
{
    private MockObject&MailerInterface $mailer;
    private MailingHelper $mailingHelper;

    protected function setUp(): void
    {
        $this->mailer = $this->createMock(MailerInterface::class);
        $this->mailingHelper = new MailingHelper($this->mailer);
    }

    public function testSendTemplatedEmailSuccessfully(): void
    {
        $this->mailer
            ->expects($this->once())
            ->method('send')
            ->with($this->callback(function ($email) {
                $this->assertInstanceOf(TemplatedEmail::class, $email);
                $this->assertSame(['test@example.com'], array_map(fn($addr) => $addr->getAddress(), $email->getTo()));
                $this->assertSame('Test Subject', $email->getSubject());
                $this->assertSame('emails/test.html.twig', $email->getHtmlTemplate());
                $this->assertSame(['name' => 'John'], $email->getContext());
                return true;
            }));

        $this->mailingHelper->sendTemplatedEmail(
            ['test@example.com'],
            'Test Subject',
            'emails/test.html.twig',
            ['name' => 'John']
        );
    }

    public function testSendTemplatedEmailThrowsMailingHelperException(): void
    {
        $this->mailer
            ->expects($this->once())
            ->method('send')
            ->willThrowException($this->createMock(TransportExceptionInterface::class));

        $this->expectException(MailingHelperException::class);

        $this->mailingHelper->sendTemplatedEmail(
            ['fail@example.com'],
            'Fail Subject',
            'emails/fail.html.twig',
            ['error' => true]
        );
    }
}
