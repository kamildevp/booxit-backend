<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Entity;

use App\DTO\EmailConfirmation\ValidateEmailConfirmationDTO;
use App\Entity\EmailConfirmation;
use App\Entity\User;
use App\Enum\EmailConfirmation\EmailConfirmationStatus;
use App\Exceptions\VerifyEmailConfirmationException;
use App\Repository\EmailConfirmationRepository;
use App\Service\Entity\EmailConfirmationService;
use App\Service\EmailConfirmation\EmailConfirmationHandlerInterface;
use DateTime;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\MessageBusInterface;

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

    public function testCreateEmailConfirmationReturnsNewEmailConfirmation(): void
    {
        $userMock = $this->createMock(User::class);
        $email = 'test@example.com';
        $verificationHandler = 'handler';
        $type = 'signup';
        $expiryDate = new DateTime();
        $params = ['test'];

        $this->emailConfirmationRepositoryMock
            ->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(EmailConfirmation::class), true);

        $result = $this->emailConfirmationService->createEmailConfirmation($email, $verificationHandler, $type, $userMock, $expiryDate, $params);

        $this->assertInstanceOf(EmailConfirmation::class, $result);
        $this->assertEquals($userMock, $result->getCreator());
        $this->assertEquals($email, $result->getEmail());
        $this->assertEquals($verificationHandler, $result->getVerificationHandler());
        $this->assertEquals($type, $result->getType());
        $this->assertEquals($expiryDate, $result->getExpiryDate());
        $this->assertEquals($params, $result->getParams());
    }

    public function testValidateEmailConfirmationReturnsTrueWhenValid(): void
    {
        $dto = new ValidateEmailConfirmationDTO(1, (new DateTime('+1 day'))->getTimestamp(), 'type', 'token', 'signature');

        $emailConfirmationMock = $this->createMock(EmailConfirmation::class);

        $this->emailConfirmationRepositoryMock
            ->method('findOneBy')
            ->with(['id' => $dto->id, 'status' => EmailConfirmationStatus::PENDING->value])
            ->willReturn($emailConfirmationMock);
        $this->emailConfirmationHandlerMock->expects($this->once())
            ->method('validateEmailConfirmation')
            ->with($emailConfirmationMock, $dto->token, $dto->_hash, $dto->expires, $dto->type)
            ->willReturn(true);

        $result = $this->emailConfirmationService->validateEmailConfirmation($dto);

        $this->assertTrue($result);
    }

    public function testValidateEmailConfirmationReturnsFalseWhenInvalid(): void
    {
        $dto = new ValidateEmailConfirmationDTO(1, (new DateTime('+1 day'))->getTimestamp(), 'type', 'token', 'signature');

        $emailConfirmationMock = $this->createMock(EmailConfirmation::class);
        $this->emailConfirmationRepositoryMock
            ->method('findOneBy')
            ->with(['id' => $dto->id, 'status' => EmailConfirmationStatus::PENDING->value])
            ->willReturn($emailConfirmationMock);
        $this->emailConfirmationHandlerMock->expects($this->once())
            ->method('validateEmailConfirmation')
            ->with($emailConfirmationMock, $dto->token, $dto->_hash, $dto->expires, $dto->type)
            ->willReturn(false);

        $result = $this->emailConfirmationService->validateEmailConfirmation($dto);

        $this->assertFalse($result);
    }

    public function testResolveEmailConfirmationThrowsExceptionWhenNotFound(): void
    {
        $dto = new ValidateEmailConfirmationDTO(1, (new DateTime('+1 day'))->getTimestamp(), 'type', 'token', 'signature');
        $this->emailConfirmationRepositoryMock
            ->method('findOneBy')
            ->with(['id' => $dto->id, 'status' => EmailConfirmationStatus::PENDING->value])
            ->willReturn(null);

        $this->expectException(VerifyEmailConfirmationException::class);
        $this->emailConfirmationService->resolveEmailConfirmation(
            $dto->id,
            $dto->token,
            $dto->_hash,
            $dto->expires,
            $dto->type
        );
    }

    public function testResolveEmailConfirmationReturnsEntityWhenValid(): void
    {
        $dto = new ValidateEmailConfirmationDTO(1, (new DateTime('+1 day'))->getTimestamp(), 'type', 'token', 'signature');

        $emailConfirmationMock = $this->createMock(EmailConfirmation::class);

        $this->emailConfirmationRepositoryMock
            ->method('findOneBy')
            ->with(['id' => $dto->id, 'status' => EmailConfirmationStatus::PENDING->value])
            ->willReturn($emailConfirmationMock);
        $this->emailConfirmationHandlerMock->expects($this->once())
            ->method('validateEmailConfirmation')
            ->with($emailConfirmationMock, $dto->token, $dto->_hash, $dto->expires, $dto->type)
            ->willReturn(true);

        $result = $this->emailConfirmationService->resolveEmailConfirmation(
            $dto->id,
            $dto->token,
            $dto->_hash,
            $dto->expires,
            $dto->type
        );

        $this->assertSame($emailConfirmationMock, $result);
    }
}
