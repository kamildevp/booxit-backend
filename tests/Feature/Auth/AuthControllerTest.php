<?php

declare(strict_types=1);

namespace App\Tests\Feature\Auth;

use App\DataFixtures\Test\Auth\AuthRefreshFixtures;
use App\Repository\RefreshTokenRepository;
use App\Tests\Feature\Auth\DataProvider\AuthGoogleLoginDataProvider;
use App\Tests\Utils\Attribute\Fixtures;
use App\Tests\Feature\Auth\DataProvider\AuthLoginDataProvider;
use App\Tests\Feature\Auth\DataProvider\AuthLogoutDataProvider;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use App\Tests\Utils\BaseWebTestCase;
use Exception;
use League\OAuth2\Client\Provider\Google;
use League\OAuth2\Client\Provider\GoogleUser;
use League\OAuth2\Client\Token\AccessToken;
use PHPUnit\Framework\MockObject\MockObject;

class AuthControllerTest extends BaseWebTestCase
{
    protected RefreshTokenRepository $refreshTokenRepository;
    protected Google&MockObject $googleClientProviderMock;
    private string $redirectUrl = 'https://test.com';

    protected function setUp(): void
    {
        parent::setUp();
        $this->googleClientProviderMock = $this->createMock(Google::class);
        $this->client->getContainer()->set(Google::class, $this->googleClientProviderMock);
        $this->refreshTokenRepository = $this->container->get(RefreshTokenRepository::class);
        $_ENV['TEST_GOOGLE_AUTH_HANDLER_REDIRECT_URL'] = $this->redirectUrl;
    }

    #[DataProviderExternal(AuthLoginDataProvider::class, 'validDataCases')]
    public function testLogin(array $params): void
    {
        $responseData = $this->getSuccessfulResponseData($this->client,'POST', '/api/auth/login', $params);
        $this->assertArrayHasKey('access_token', $responseData);
        $this->assertArrayHasKey('refresh_token', $responseData);
    }

    #[DataProviderExternal(AuthLoginDataProvider::class, 'invalidCredentialsDataCases')]
    public function testLoginWithInvalidCredentials(array $params): void
    {
        $responseData = $this->getFailureResponseData($this->client, 'POST', '/api/auth/login', $params, expectedCode: 401);
        $this->assertEquals('Invalid credentials', $responseData['message']);
    }

    #[Fixtures([AuthRefreshFixtures::class])]
    public function testRefresh(): void
    {
        $refreshToken = $this->refreshTokenRepository->findOneBy(['appUser' => $this->user]);
        $params = ['refresh_token' => $refreshToken->getValue()];
        $responseData = $this->getSuccessfulResponseData($this->client, 'POST', '/api/auth/refresh', $params);
        $this->assertArrayHasKey('access_token', $responseData);
        $this->assertArrayHasKey('refresh_token', $responseData);
        $this->assertNotEquals($params['refresh_token'], $responseData['refresh_token']);
    }

    public function testRefreshWithInvalidRefreshToken(): void
    {
        $params = ['refresh_token' => 'invalid'];
        $responseData = $this->getFailureResponseData($this->client, 'POST', '/api/auth/refresh', $params, expectedCode: 401);
        $this->assertEquals('Invalid or expired refresh token', $responseData['message']);
    }

    #[Fixtures([AuthRefreshFixtures::class])]
    public function testRefreshWithUsedRefreshToken(): void
    {
        $refreshToken = $this->refreshTokenRepository->findOneBy(['appUser' => $this->user]);
        $params = ['refresh_token' => $refreshToken->getValue()];
        $this->getSuccessfulResponseData($this->client, 'POST', '/api/auth/refresh', $params);

        $responseData = $this->getFailureResponseData($this->client, 'POST', '/api/auth/refresh', $params, expectedCode: 401);
        $this->assertEquals('Invalid or expired refresh token', $responseData['message']);
    }

    #[Fixtures([AuthRefreshFixtures::class])]
    #[DataProviderExternal(AuthLogoutDataProvider::class, 'validDataCases')]
    public function testLogout(array $params, int $expectedRefreshTokensCount): void
    {
        $this->fullLogin($this->client); 
        $responseData = $this->getSuccessfulResponseData($this->client, 'POST', '/api/auth/logout', $params);

        $this->assertEquals('Logged out successfully', $responseData['message']);
        $this->assertCount($expectedRefreshTokensCount, $this->user->getRefreshTokens());
    }

    #[DataProviderExternal(AuthGoogleLoginDataProvider::class, 'validDataCases')]
    public function testGoogleLogin(array $params): void
    {
        $emailMock = 'googleuser@example.com';
        $nameMock = 'User Mock';
        $localeMock = 'en-US';
        $googleUserIdMock = '42141142';

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
                    'code' => $params['code'],
                    'code_verifier' => $params['code_verifier'],
                    'redirect_uri' => $this->redirectUrl
                ]
            )
            ->willReturn($tokenMock);

        $this->googleClientProviderMock
            ->expects($this->once())
            ->method('getResourceOwner')
            ->with($tokenMock)
            ->willReturn($googleUserMock);

        $responseData = $this->getSuccessfulResponseData($this->client, 'POST', '/api/auth/google_login', $params);
        $this->assertArrayHasKey('access_token', $responseData);
        $this->assertArrayHasKey('refresh_token', $responseData);
    }

    #[DataProviderExternal(AuthGoogleLoginDataProvider::class, 'invalidAuthParametersDataCases')]
    public function testGoogleLoginWithInvalidAuthParameters(array $params): void
    {
        $responseData = $this->getFailureResponseData($this->client, 'POST', '/api/auth/google_login', $params, expectedCode: 401);
        $this->assertEquals('Invalid auth parameters', $responseData['message']);
    }

    #[DataProviderExternal(AuthGoogleLoginDataProvider::class, 'validDataCases')]
    public function testGoogleLoginTokenExchangeFailure(array $params): void
    {
        $tokenMock = $this->createMock(AccessToken::class);
        $this->googleClientProviderMock
            ->expects($this->once())
            ->method('getAccessToken')
            ->with(
                'authorization_code', 
                [
                    'code' => $params['code'],
                    'code_verifier' => $params['code_verifier'],
                    'redirect_uri' => $this->redirectUrl
                ]
            )
            ->willReturn($tokenMock);

        $this->googleClientProviderMock
            ->expects($this->once())
            ->method('getResourceOwner')
            ->with($tokenMock)
            ->willThrowException(new Exception());

        $responseData = $this->getFailureResponseData($this->client, 'POST', '/api/auth/google_login', $params, expectedCode: 401);
        $this->assertEquals('Invalid credentials', $responseData['message']);
    }

    #[DataProviderExternal(AuthGoogleLoginDataProvider::class, 'validDataCases')]
    public function testGoogleLoginResourceOwnerFetchFailure(array $params): void
    {
        $this->googleClientProviderMock
            ->expects($this->once())
            ->method('getAccessToken')
            ->willThrowException(new Exception());

        $this->googleClientProviderMock
            ->expects($this->never())
            ->method('getResourceOwner');

        $responseData = $this->getFailureResponseData($this->client, 'POST', '/api/auth/google_login', $params, expectedCode: 401);
        $this->assertEquals('Invalid credentials', $responseData['message']);
    }
}
