<?php

declare(strict_types=1);

namespace App\Tests\Service\Entity;

use App\DTO\EmailConfirmation\VerifyEmailConfirmationDTO;
use App\DTO\User\UserCreateDTO;
use App\DTO\User\UserPatchDTO;
use App\DTO\User\UserResetPasswordDTO;
use App\DTO\User\UserResetPasswordRequestDTO;
use App\Entity\EmailConfirmation;
use App\Entity\RefreshToken;
use App\Entity\User;
use App\Enum\EmailConfirmationType;
use App\Exceptions\InvalidActionException;
use App\Exceptions\VerifyEmailConfirmationException;
use App\Repository\EmailConfirmationRepository;
use App\Repository\RefreshTokenRepository;
use App\Repository\UserRepository;
use App\Service\Entity\EmailConfirmationService;
use App\Service\Entity\UserService;
use App\Service\EntitySerializer\EntitySerializerInterface;
use DateTime;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserServiceTest extends TestCase
{
    private MockObject&EntitySerializerInterface $serializerMock;
    private MockObject&EmailConfirmationService $emailConfirmationServiceMock;
    private MockObject&UserPasswordHasherInterface $hasherMock;

    private MockObject&UserRepository $userRepositoryMock;
    private MockObject&EmailConfirmationRepository $emailConfirmationRepositoryMock;
    private MockObject&RefreshTokenRepository $refreshTokenRepositoryMock;

    private UserService $userService;

    protected function setUp(): void
    {
        $this->serializerMock = $this->createMock(EntitySerializerInterface::class);
        $this->emailConfirmationServiceMock = $this->createMock(EmailConfirmationService::class);
        $this->hasherMock = $this->createMock(UserPasswordHasherInterface::class);

        $this->userRepositoryMock = $this->createMock(UserRepository::class);
        $this->emailConfirmationRepositoryMock = $this->createMock(EmailConfirmationRepository::class);
        $this->refreshTokenRepositoryMock = $this->createMock(RefreshTokenRepository::class);


        $this->userService = new UserService(
            $this->serializerMock,
            $this->emailConfirmationServiceMock,
            $this->hasherMock,
            $this->userRepositoryMock,
            $this->emailConfirmationRepositoryMock,
            $this->refreshTokenRepositoryMock
        );
    }

    public function testCreateUser(): void
    {
        $dto = new UserCreateDTO('test', 'test@example.com', 'handler', 'pass');
        $userMock = $this->createMock(User::class);
        $hashedPasswordMock = 'hashed-pass';

        $this->serializerMock
            ->method('parseToEntity')
            ->with($dto->toArray(['password']), User::class)
            ->willReturn($userMock);

        $this->hasherMock
            ->method('hashPassword')
            ->with($userMock, $dto->password)
            ->willReturn($hashedPasswordMock);
        
        $userMock->expects($this->once())->method('setPassword')->with($hashedPasswordMock);
        $userMock->expects($this->once())->method('setVerified')->with(false);

        $this->userRepositoryMock->expects($this->once())->method('save')->with($userMock, true);

        $this->emailConfirmationServiceMock->expects($this->once())
            ->method('setupEmailConfirmation')
            ->with($userMock, $dto->email, $dto->verificationHandler, EmailConfirmationType::USER_VERIFICATION->value, true);

        $result = $this->userService->createUser($dto);

        $this->assertSame($userMock, $result);
    }

    public function testPatchUser(): void
    {
        $dto = new UserPatchDTO('new test', 'user@example.com', 'handler');
        $userMock = $this->createMock(User::class);

        $userMock->method('getEmail')->willReturn('user@example.com');

        $this->serializerMock
            ->method('parseToEntity')
            ->with($dto, $userMock)
            ->willReturn($userMock);

        $this->userRepositoryMock->expects($this->once())->method('save')->with($userMock, true);

        $this->emailConfirmationServiceMock
            ->expects($this->never())
            ->method('setupEmailConfirmation');

        $this->userService->patchUser($userMock, $dto);
    }

    public function testPatchUserTriggersEmailConfirmationIfChanged(): void
    {
        $dto = new UserPatchDTO('test', 'new@example.com', 'handler');
        $userMock = $this->createMock(User::class);

        $userMock->method('getEmail')->willReturn('old@example.com');

        $this->serializerMock
            ->method('parseToEntity')
            ->with($dto, $userMock)
            ->willReturn($userMock);

        $this->userRepositoryMock->expects($this->once())->method('save')->with($userMock, true);

        $this->emailConfirmationServiceMock
            ->expects($this->once())
            ->method('setupEmailConfirmation')
            ->with($userMock, $dto->email, $dto->verificationHandler, EmailConfirmationType::EMAIL_VERIFICATION->value);

        $this->userService->patchUser($userMock, $dto);
    }

    public function testChangeUserPasswordWithInvalidRefreshToken(): void
    {
        $userMock = $this->createMock(User::class);
        $userMock->method('getId')->willReturn(1);

        $refreshTokenUserMock = $this->createMock(User::class);
        $refreshTokenUserMock->method('getId')->willReturn(2);
        $refreshTokenMock = $this->createMock(RefreshToken::class);
        $refreshTokenMock->method('getAppUser')->willReturn($refreshTokenUserMock);

        $this->expectException(InvalidActionException::class);

        $this->userService->changeUserPassword($userMock, 'new-pass', true, $refreshTokenMock);
    }

    public function testChangeUserPasswordWithValidInput(): void
    {
        $userMock = $this->createMock(User::class);
        $userMock->method('getId')->willReturn(1);

        $refreshTokenIdMock = 1;
        $refreshTokenMock = $this->createMock(RefreshToken::class);
        $refreshTokenMock->method('getAppUser')->willReturn($userMock);
        $refreshTokenMock->method('getId')->willReturn($refreshTokenIdMock);

        $password = 'pass';
        $hashedPassword = 'hashed-pass';

        $this->hasherMock->method('hashPassword')->with($userMock, $password)->willReturn($hashedPassword);
        $userMock->expects($this->once())->method('setPassword')->with($hashedPassword);

        $this->userRepositoryMock->expects($this->once())->method('save')->with($userMock, true);

        $this->refreshTokenRepositoryMock
            ->expects($this->once())
            ->method('removeAllUserRefreshTokensExceptIds')
            ->with($userMock, [$refreshTokenIdMock]);

        $this->userService->changeUserPassword($userMock, $password, true, $refreshTokenMock);
    }

    public function testVerifyUserEmailFails(): void
    {
        $dto = new VerifyEmailConfirmationDTO(1, (new DateTime('+1 day'))->getTimestamp(), 'type', 'token', 'signature');

        $this->emailConfirmationServiceMock
            ->method('resolveEmailConfirmation')
            ->with($dto)
            ->willThrowException(new VerifyEmailConfirmationException());

        $this->assertFalse($this->userService->verifyUserEmail($dto));
    }

    public function testVerifyUserEmailSucceeds(): void
    {
        $dto = new VerifyEmailConfirmationDTO(1, (new DateTime('+1 day'))->getTimestamp(), 'type', 'token', 'signature');

        $userMock = $this->createMock(User::class);
        $emailConfirmationMock = $this->createMock(EmailConfirmation::class);
        $emailConfirmationMock->method('getCreator')->willReturn($userMock);
        $emailMock = 'verified@example.com';
        $emailConfirmationMock->method('getEmail')->willReturn($emailMock);


        $this->emailConfirmationServiceMock->method('resolveEmailConfirmation')->with($dto)->willReturn($emailConfirmationMock);

        $userMock->expects($this->once())->method('setEmail')->with($emailMock);
        $userMock->expects($this->once())->method('setVerified')->with(true);
        $userMock->expects($this->once())->method('setExpiryDate')->with(null);

        $this->emailConfirmationRepositoryMock->expects($this->once())->method('remove')->with($emailConfirmationMock, true);

        $result = $this->userService->verifyUserEmail($dto);

        $this->assertTrue($result);
    }

    public function testHandleResetPasswordRequestCreatesNewEmailConfirmationWhenNoActiveConfirmationExists(): void
    {
        $dto = new UserResetPasswordRequestDTO('reset@example.com', 'handler');
        $userMock = $this->createMock(User::class);
        $userMock->method('getEmail')->willReturn($dto->email);

        $this->userRepositoryMock->method('findOneBy')->with(['email' => $dto->email])->willReturn($userMock);
        $this->emailConfirmationRepositoryMock
            ->method('findActiveUserEmailConfirmationByType')
            ->with($userMock, EmailConfirmationType::PASSWORD_RESET->value)
            ->willReturn(null);

        $this->emailConfirmationServiceMock
            ->expects($this->once())
            ->method('setupEmailConfirmation')
            ->with($userMock, $dto->email, $dto->verificationHandler, EmailConfirmationType::PASSWORD_RESET->value);

        $this->userService->handleResetUserPasswordRequest($dto);
    }


    public function testHandleResetPasswordRequestSkipsEmailConfirmationCreationIfActiveConfirmationExists(): void
    {
        $dto = new UserResetPasswordRequestDTO('reset@example.com', 'handler');
        $userMock = $this->createMock(User::class);
        $emailConfirmationMock = $this->createMock(EmailConfirmation::class);

        $this->userRepositoryMock->method('findOneBy')->with(['email' => $dto->email])->willReturn($userMock);
        $this->emailConfirmationRepositoryMock
            ->method('findActiveUserEmailConfirmationByType')
            ->with($userMock, EmailConfirmationType::PASSWORD_RESET->value)
            ->willReturn($emailConfirmationMock);

        $this->emailConfirmationServiceMock
            ->expects($this->never())
            ->method('setupEmailConfirmation');

        $this->userService->handleResetUserPasswordRequest($dto);
    }

    public function testResetUserPasswordFailsOnInvalidInput(): void
    {
        $dto = new UserResetPasswordDTO(1, (new DateTime('+1 day'))->getTimestamp(), 'type', 'token', 'signature', 'pass');

        $this->emailConfirmationServiceMock
            ->method('resolveEmailConfirmation')
            ->willThrowException(new VerifyEmailConfirmationException());

        $this->assertFalse($this->userService->resetUserPassword($dto));
    }

    public function testResetUserPasswordSuccess(): void
    {
        $dto = new UserResetPasswordDTO(1, (new DateTime('+1 day'))->getTimestamp(), 'type', 'token', 'signature', 'pass');

        $userMock = $this->createMock(User::class);

        $emailConfirmationMock = $this->createMock(EmailConfirmation::class);
        $emailConfirmationMock->method('getCreator')->willReturn($userMock);

        $this->emailConfirmationServiceMock->method('resolveEmailConfirmation')->willReturn($emailConfirmationMock);

        $password = 'pass';
        $hashedPassword = 'hashed-pass';

        $this->hasherMock->method('hashPassword')->with($userMock, $password)->willReturn($hashedPassword);
        
        $userMock->expects($this->once())->method('setPassword')->with($hashedPassword);
        $this->userRepositoryMock->expects($this->once())->method('save')->with($userMock, true);
        $this->refreshTokenRepositoryMock->expects($this->once())->method('removeAllUserRefreshTokens')->with($userMock);
        $this->emailConfirmationRepositoryMock->expects($this->once())->method('remove')->with($emailConfirmationMock, true);

        $result = $this->userService->resetUserPassword($dto);
        $this->assertTrue($result);
    }
}
