<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Auth\Social;

use App\Entity\User;
use App\Enum\Auth\Social\SocialAuthProvider;
use App\Enum\TranslationsLocale;
use App\Repository\UserRepository;
use App\Service\Auth\Social\DTO\SocialOwnerDTO;
use App\Service\Auth\Social\Exception\ResolveAuthHandlerRedirectUrlException;
use App\Service\Auth\Social\Exception\SocialAuthFailedException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AbstractProviderTest extends TestCase
{
    private MockObject&UserRepository $userRepositoryMock;
    private MockObject&ValidatorInterface $validatorMock;
    private TestProvider $provider;
    private string $authHandler = 'TEST';
    private string $redirectUrl = 'https://test.com';

    protected function setUp(): void
    {
        $_ENV['TEST_GOOGLE_AUTH_HANDLER_REDIRECT_URL'] = $this->redirectUrl;

        $this->userRepositoryMock = $this->createMock(UserRepository::class);
        $this->validatorMock = $this->createMock(ValidatorInterface::class);
        $this->provider = new TestProvider($this->userRepositoryMock, $this->validatorMock);
    }

    public function testResolveAuthHandlerRedirectUrlForDefinedAuthHandler(): void
    {
        $url = $this->provider->resolveAuthHandlerRedirectUrl($this->authHandler, SocialAuthProvider::GOOGLE);
        $this->assertEquals($this->redirectUrl, $url);
    }

    public function testResolveAuthHandlerRedirectUrlThrowsExceptionForUndefinedAuthHandler(): void
    {
        $this->expectException(ResolveAuthHandlerRedirectUrlException::class);
        $this->provider->resolveAuthHandlerRedirectUrl('INVALID', SocialAuthProvider::GOOGLE);
    }

    public function testResolveUserReturnsExistingUserWhenEmailMatches(): void
    {
        $authProvider = SocialAuthProvider::GOOGLE;
        $emailMock = 'user@example.com';
        $nameMock = 'User Mock';
        $localeMock = 'en-US';
        $authProviderUserIdMock = '42141142';

        $userMock = $this->createMock(User::class);
        $userMock->expects($this->once())->method('isDeleted')->willReturn(false);
        $userMock->expects($this->once())->method('isVerified')->willReturn(true);

        $this->userRepositoryMock
            ->method('findOneByFieldValue')
            ->with('email', $emailMock, [], ['softdeleteable', 'verifiable'])
            ->willReturn($userMock);

        $this->validatorMock
            ->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList([]));

        $result = $this->provider->resolveUserWrapper($authProvider, $emailMock, $nameMock, $localeMock, $authProviderUserIdMock);
        $this->assertSame($userMock, $result);
    }

    public function testResolveUserReturnsNewUserWhenEmailDoesNotMatch(): void
    {
        $authProvider = SocialAuthProvider::GOOGLE;
        $emailMock = 'user@example.com';
        $nameMock = 'User Mock';
        $localeMock = 'en-US';
        $authProviderUserIdMock = '42141142';

        $this->userRepositoryMock
            ->method('findOneByFieldValue')
            ->with('email', $emailMock, [], ['softdeleteable', 'verifiable'])
            ->willReturn(null);

        $this->userRepositoryMock
            ->expects($this->once())
            ->method('save')
            ->with(
                $this->callback(fn($arg) => 
                    $arg instanceof User &&
                    $arg->getEmail() === $emailMock &&
                    $arg->getName() === $nameMock &&
                    $arg->getAuthProvider() === $authProvider->value &&
                    $arg->getAuthProviderUserId() === $authProviderUserIdMock &&
                    $arg->getLanguagePreference() === 'en' &&
                    $arg->isVerified() === true
                ),
                true
            );

        $this->validatorMock
            ->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList([]));

        $result = $this->provider->resolveUserWrapper($authProvider, $emailMock, $nameMock, $localeMock, $authProviderUserIdMock);
        $this->assertInstanceOf(User::class, $result);
    }

    public function testResolveUserThrowsExceptionWhenUserAccountHasBeenDeleted(): void
    {
        $authProvider = SocialAuthProvider::GOOGLE;
        $emailMock = 'user@example.com';
        $nameMock = 'User Mock';
        $localeMock = 'en-US';
        $authProviderUserIdMock = '42141142';

        $userMock = $this->createMock(User::class);
        $userMock->expects($this->once())->method('isDeleted')->willReturn(true);

        $this->userRepositoryMock
            ->method('findOneByFieldValue')
            ->with('email', $emailMock, [], ['softdeleteable', 'verifiable'])
            ->willReturn($userMock);

        $this->validatorMock
            ->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList([]));

        $this->expectException(SocialAuthFailedException::class);
        $this->provider->resolveUserWrapper($authProvider, $emailMock, $nameMock, $localeMock, $authProviderUserIdMock);
    }

    public function testResolveUserMarksAccountAsVerifiedIfNotAlreadyVerified(): void
    {
        $authProvider = SocialAuthProvider::GOOGLE;
        $emailMock = 'user@example.com';
        $nameMock = 'User Mock';
        $localeMock = 'en-US';
        $authProviderUserIdMock = '42141142';

        $userMock = $this->createMock(User::class);
        $userMock->expects($this->once())->method('isDeleted')->willReturn(false);
        $userMock->expects($this->once())->method('isVerified')->willReturn(false);
        $userMock->expects($this->once())->method('setVerified')->with(true);

        $this->userRepositoryMock
            ->method('findOneByFieldValue')
            ->with('email', $emailMock, [], ['softdeleteable', 'verifiable'])
            ->willReturn($userMock);

        $this->userRepositoryMock
            ->method('save')
            ->with($userMock, true);

        $this->validatorMock
            ->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList([]));

        $this->provider->resolveUserWrapper($authProvider, $emailMock, $nameMock, $localeMock, $authProviderUserIdMock);
    }

    public function testCreateUserCreatesUserFromSocialOwnerDTO(): void
    {
        $authProvider = SocialAuthProvider::GOOGLE;
        $dto = new SocialOwnerDTO(
            'user@example.com',
            'User Mock',
            'en',
            '42141142'
        );

        $this->userRepositoryMock
            ->expects($this->once())
            ->method('save')
            ->with(
                $this->callback(fn($arg) => 
                    $arg instanceof User &&
                    $arg->getEmail() === $dto->email &&
                    $arg->getName() === $dto->name &&
                    $arg->getAuthProvider() === $authProvider->value &&
                    $arg->getAuthProviderUserId() === $dto->id &&
                    $arg->getLanguagePreference() === 'en' &&
                    $arg->isVerified() === true
                ),
                true
            );

        $result = $this->provider->createUserWrapper($dto, $authProvider);
        $this->assertInstanceOf(User::class, $result);
    }

    public function testResolveUserLocaleReturnsValidLocale(): void
    {
        $result = $this->provider->resolveUserLocaleWrapper('pl-PL');
        $this->assertEquals(TranslationsLocale::PL, $result);
    }

    public function testResolveUserLocaleReturnsDefaultLocaleWhenNoMatchIsFound(): void
    {
        $result = $this->provider->resolveUserLocaleWrapper('fake');
        $this->assertEquals(TranslationsLocale::EN, $result);
    }

    public function testResolveUserLocaleReturnsDefaultLocaleWhenProviderLocaleIsNull(): void
    {
        $result = $this->provider->resolveUserLocaleWrapper(null);
        $this->assertEquals(TranslationsLocale::EN, $result);
    }

    public function testParseOwnerInfoReturnsSocialOwnerDTOForValidData(): void
    {
        $this->validatorMock
            ->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList([]));

        $result = $this->provider->parseOwnerInfoWrapper(
            'user@example.com',
            'User Mock',
            'en',
            '42141142'
        );

        $this->assertInstanceOf(SocialOwnerDTO::class, $result);
        $this->assertEquals('user@example.com', $result->email);
        $this->assertEquals('User Mock', $result->name);
        $this->assertEquals('en', $result->locale);
        $this->assertEquals('42141142', $result->id);
    }

    public function testParseOwnerInfoThrowsExceptionForInvalidData(): void
    {
        $violationMock = $this->createMock(ConstraintViolationInterface::class);
        $this->validatorMock
            ->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList([$violationMock]));

        $this->expectException(SocialAuthFailedException::class);
        $this->provider->parseOwnerInfoWrapper(
            'not-email.com',
            'User Mock',
            'en',
            '42141142'
        );
    }

    public function testParseOwnerInfoTrimsTooLongName(): void
    {
        $this->validatorMock
            ->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList([]));

        $result = $this->provider->parseOwnerInfoWrapper(
            'user@example.com',
            str_repeat('a', 55),
            'en',
            '42141142'
        );

        $this->assertEquals(str_repeat('a', 50), $result->name);
    }
}
