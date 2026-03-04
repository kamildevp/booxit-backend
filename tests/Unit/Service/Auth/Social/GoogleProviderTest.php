<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Auth\Social;

use App\Entity\User;
use App\Enum\Auth\Social\SocialAuthProvider;
use App\Repository\UserRepository;
use App\Service\Auth\Social\Exception\SocialAuthFailedException;
use App\Service\Auth\Social\GoogleProvider;
use Exception;
use League\OAuth2\Client\Provider\Google;
use League\OAuth2\Client\Provider\GoogleUser;
use League\OAuth2\Client\Token\AccessToken;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class GoogleProviderTest extends TestCase
{
    private MockObject&UserRepository $userRepositoryMock;
    private MockObject&ValidatorInterface $validatorMock;
    private MockObject&Google $googleClientProviderMock;
    private GoogleProvider $provider;
    private string $authHandler = 'TEST';
    private string $redirectUrl = 'https://test.com';

    protected function setUp(): void
    {
        $_ENV['TEST_GOOGLE_AUTH_HANDLER_REDIRECT_URL'] = $this->redirectUrl;

        $this->userRepositoryMock = $this->createMock(UserRepository::class);
        $this->validatorMock = $this->createMock(ValidatorInterface::class);
        $this->googleClientProviderMock = $this->createMock(Google::class);
        $this->provider = new GoogleProvider($this->googleClientProviderMock, $this->userRepositoryMock, $this->validatorMock);
    }

    public function testGetUserReturnsExistingUserWhenGoogleAccountEmailMatches(): void
    {
        $emailMock = 'user@example.com';
        $nameMock = 'User Mock';
        $localeMock = 'en-US';
        $googleUserIdMock = '42141142';
        $codeMock = 'codeMock';
        $pkceCodeMock = 'codeVerifierMock';

        $tokenMock = $this->createMock(AccessToken::class);
        $googleUserMock = $this->createMock(GoogleUser::class);
        $googleUserMock->method('isEmailTrustworthy')->willReturn(true);
        $googleUserMock->method('getEmail')->willReturn($emailMock);
        $googleUserMock->method('getName')->willReturn($nameMock);
        $googleUserMock->method('getLocale')->willReturn($localeMock);
        $googleUserMock->method('getId')->willReturn($googleUserIdMock);

        $this->googleClientProviderMock
            ->expects($this->once())
            ->method('getAccessToken')
            ->with(
                'authorization_code', 
                [
                    'code' => $codeMock,
                    'code_verifier' => $pkceCodeMock,
                    'redirect_uri' => $this->redirectUrl
                ]
            )
            ->willReturn($tokenMock);

        $this->googleClientProviderMock
            ->expects($this->once())
            ->method('getResourceOwner')
            ->with($tokenMock)
            ->willReturn($googleUserMock);
        

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

        $result = $this->provider->getUser($this->authHandler, $codeMock, $pkceCodeMock);
        $this->assertSame($userMock, $result);
    }

    public function testGetUserReturnsNewUserWhenGoogleAccountEmailDoesNotMatch(): void
    {
        $emailMock = 'user@example.com';
        $nameMock = 'User Mock';
        $localeMock = 'en-US';
        $googleUserIdMock = '42141142';
        $codeMock = 'codeMock';
        $pkceCodeMock = 'codeVerifierMock';

        $tokenMock = $this->createMock(AccessToken::class);
        $googleUserMock = $this->createMock(GoogleUser::class);
        $googleUserMock->method('isEmailTrustworthy')->willReturn(true);
        $googleUserMock->method('getEmail')->willReturn($emailMock);
        $googleUserMock->method('getName')->willReturn($nameMock);
        $googleUserMock->method('getLocale')->willReturn($localeMock);
        $googleUserMock->method('getId')->willReturn($googleUserIdMock);

        $this->googleClientProviderMock
            ->expects($this->once())
            ->method('getAccessToken')
            ->with(
                'authorization_code', 
                [
                    'code' => $codeMock,
                    'code_verifier' => $pkceCodeMock,
                    'redirect_uri' => $this->redirectUrl
                ]
            )
            ->willReturn($tokenMock);

        $this->googleClientProviderMock
            ->expects($this->once())
            ->method('getResourceOwner')
            ->with($tokenMock)
            ->willReturn($googleUserMock);

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
                    $arg->getAuthProvider() === SocialAuthProvider::GOOGLE->value &&
                    $arg->getAuthProviderUserId() === $googleUserIdMock &&
                    $arg->getLanguagePreference() === 'en' &&
                    $arg->isVerified() === true
                ),
                true
            );

        $this->validatorMock
            ->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList([]));

        $result = $this->provider->getUser($this->authHandler, $codeMock, $pkceCodeMock);
        $this->assertInstanceOf(User::class, $result);
    }

    public function testGetUserThrowsExceptionWhenGoogleTokenExchangeFails(): void
    {
        $codeMock = 'codeMock';
        $pkceCodeMock = 'codeVerifierMock';

        $this->googleClientProviderMock->method('getAccessToken')->willThrowException(new Exception());

        $this->expectException(SocialAuthFailedException::class);
        $this->provider->getUser($this->authHandler, $codeMock, $pkceCodeMock);
    }


    public function testGetUserThrowsExceptionWhenFetchingGoogleUserDataFails(): void
    {
        $codeMock = 'codeMock';
        $pkceCodeMock = 'codeVerifierMock';

        $tokenMock = $this->createMock(AccessToken::class);
        $this->googleClientProviderMock->method('getAccessToken')->willReturn($tokenMock);
        $this->googleClientProviderMock->method('getResourceOwner')->willThrowException(new Exception());

        $this->expectException(SocialAuthFailedException::class);
        $this->provider->getUser($this->authHandler, $codeMock, $pkceCodeMock);
    }
}
