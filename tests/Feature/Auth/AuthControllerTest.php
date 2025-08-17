<?php

namespace App\Tests\Feature\Auth;

use App\DataFixtures\Test\Auth\AuthRefreshFixtures;
use App\Entity\RefreshToken;
use App\Repository\RefreshTokenRepository;
use App\Tests\Feature\Auth\DataProvider\AuthLoginDataProvider;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use App\Tests\Feature\BaseWebTestCase;

class AuthControllerTest extends BaseWebTestCase
{
    protected RefreshTokenRepository $refreshTokenRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->refreshTokenRepository = $this->container->get(EntityManagerInterface::class)->getRepository(RefreshToken::class);
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

    public function testRefresh(): void
    {
        $this->dbTool->loadFixtures([
            AuthRefreshFixtures::class
        ], true);

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

    public function testRefreshWithUsedRefreshToken(): void
    {
        $this->dbTool->loadFixtures([
            AuthRefreshFixtures::class
        ], true);

        $refreshToken = $this->refreshTokenRepository->findOneBy(['appUser' => $this->user]);
        $params = ['refresh_token' => $refreshToken->getValue()];
        $this->getSuccessfulResponseData($this->client, 'POST', '/api/auth/refresh', $params);

        $responseData = $this->getFailureResponseData($this->client, 'POST', '/api/auth/refresh', $params, expectedCode: 401);
        $this->assertEquals('Invalid or expired refresh token', $responseData['message']);
    }
}
