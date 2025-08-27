<?php

declare(strict_types=1);

namespace App\Tests\Service\Entity;

use App\DTO\EmailConfirmation\VerifyEmailConfirmationDTO;
use App\Entity\EmailConfirmation;
use App\Entity\User;
use App\Exceptions\VerifyEmailConfirmationException;
use App\Message\EmailVerification;
use App\Repository\EmailConfirmationRepository;
use App\Service\Entity\EmailConfirmationService;
use App\Service\EmailConfirmation\EmailConfirmationHandlerInterface;
use DateTime;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Envelope;

class EmailConfirmationServiceTest extends TestCase
{
    private MockObject&EmailConfirmationHandlerInterface $emailConfirmationHandlerMock;
    private MockObject&MessageBusInterface $messageBusMock;
    private MockObject&EmailConfirmationRepository $emailConfirmationRepositoryMock;
    private EmailConfirmationService $emailConfirmationService;

    protected function setUp(): void
    {
        $this->emailConfirmationHandlerMock = $this->createMock(EmailConfirmationHandlerInterface::class);
        $this->messageBusMock = $this->createMock(MessageBusInterface::class);
        $this->emailConfirmationRepositoryMock = $this->createMock(EmailConfirmationRepository::class);

        $this->emailConfirmationService = new EmailConfirmationService(
            $this->emailConfirmationHandlerMock,
            $this->messageBusMock,
            $this->emailConfirmationRepositoryMock
        );
    }

    public function testSetupEmailConfirmationDispatchesMessageAndSavesEntity(): void
    {
        $userMock = $this->createMock(User::class);
        $email = 'test@example.com';
        $verificationHandler = 'handler';
        $type = 'signup';
        $emailConfirmationIdMock = 1;

        $this->emailConfirmationRepositoryMock
            ->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(EmailConfirmation::class), true)
            ->willReturnCallback(function(EmailConfirmation $emailConfirmation) use ($emailConfirmationIdMock) {
                $ref = new \ReflectionClass($emailConfirmation);
                $prop = $ref->getProperty('id');
                $prop->setAccessible(true);
                $prop->setValue($emailConfirmation, $emailConfirmationIdMock);
            });

        $this->messageBusMock
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(function ($message) {
                return $message instanceof EmailVerification;
            }))
            ->willReturn(new Envelope(new EmailVerification($emailConfirmationIdMock, false)));

        $this->emailConfirmationService->setupEmailConfirmation($userMock, $email, $verificationHandler, $type);
    }

    public function testValidateEmailConfirmationReturnsTrueWhenValid(): void
    {
        $dto = new VerifyEmailConfirmationDTO(1, (new DateTime('+1 day'))->getTimestamp(), 'type', 'token', 'signature');

        $emailConfirmationMock = $this->createMock(EmailConfirmation::class);

        $this->emailConfirmationRepositoryMock->method('find')->willReturn($emailConfirmationMock);
        $this->emailConfirmationHandlerMock->expects($this->once())
            ->method('validateEmailConfirmation')
            ->with($emailConfirmationMock, $dto->token, $dto->signature, $dto->expires, $dto->type)
            ->willReturn(true);

        $result = $this->emailConfirmationService->validateEmailConfirmation($dto);

        $this->assertTrue($result);
    }

    public function testValidateEmailConfirmationReturnsFalseWhenInvalid(): void
    {
        $dto = new VerifyEmailConfirmationDTO(1, (new DateTime('+1 day'))->getTimestamp(), 'type', 'token', 'signature');

        $emailConfirmationMock = $this->createMock(EmailConfirmation::class);
        $this->emailConfirmationRepositoryMock->method('find')->willReturn($emailConfirmationMock);
        $this->emailConfirmationHandlerMock->expects($this->once())
            ->method('validateEmailConfirmation')
            ->with($emailConfirmationMock, $dto->token, $dto->signature, $dto->expires, $dto->type)
            ->willReturn(false);

        $result = $this->emailConfirmationService->validateEmailConfirmation($dto);

        $this->assertFalse($result);
    }

    public function testResolveEmailConfirmationThrowsExceptionWhenNotFound(): void
    {
        $dto = new VerifyEmailConfirmationDTO(1, (new DateTime('+1 day'))->getTimestamp(), 'type', 'token', 'signature');
        $this->emailConfirmationRepositoryMock->method('find')->willReturn(null);

        $this->expectException(VerifyEmailConfirmationException::class);
        $this->emailConfirmationService->resolveEmailConfirmation($dto);
    }

    public function testResolveEmailConfirmationReturnsEntityWhenValid(): void
    {
        $dto = new VerifyEmailConfirmationDTO(1, (new DateTime('+1 day'))->getTimestamp(), 'type', 'token', 'signature');

        $emailConfirmationMock = $this->createMock(EmailConfirmation::class);

        $this->emailConfirmationRepositoryMock->method('find')->willReturn($emailConfirmationMock);
        $this->emailConfirmationHandlerMock->expects($this->once())
            ->method('validateEmailConfirmation')
            ->with($emailConfirmationMock, $dto->token, $dto->signature, $dto->expires, $dto->type)
            ->willReturn(true);

        $result = $this->emailConfirmationService->resolveEmailConfirmation($dto);

        $this->assertSame($emailConfirmationMock, $result);
    }
}
