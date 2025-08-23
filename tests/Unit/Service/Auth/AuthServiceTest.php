<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Auth;

use App\DTO\Auth\AuthLogoutDTO;
use App\Entity\RefreshToken;
use App\Entity\User;
use App\Exceptions\InvalidObjectException;
use App\Exceptions\TokenRefreshFailedException;
use App\Repository\RefreshTokenRepository;
use App\Service\Auth\AuthService;
use DateTime;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authenticator\Token\JWTPostAuthenticationToken;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class AuthServiceTest extends TestCase
{
    private MockObject&Security $securityMock;
    private MockObject&RefreshTokenRepository $refreshTokenRepositoryMock;
    private MockObject&JWTEncoderInterface $jwtEncoderMock;
    private AuthService $authService;
    private int $refreshTokenTTL = 3600;

    protected function setUp(): void
    {
        $this->securityMock = $this->createMock(Security::class);
        $this->refreshTokenRepositoryMock = $this->createMock(RefreshTokenRepository::class);
        $this->jwtEncoderMock = $this->createMock(JWTEncoderInterface::class);
        $this->authService = new AuthService(
            $this->securityMock, 
            $this->refreshTokenRepositoryMock, 
            $this->jwtEncoderMock, 
            $this->refreshTokenTTL
        );
    }

    public function testCreateUserRefreshToken(): void
    {
        $userMock = $this->createMock(User::class);
        $userId = 1;
        $userRoles = ['USER'];
        $refreshTokenId = 1;
        $tokenMock = 'tokenMock';

        $userMock->expects($this->once())
            ->method('getId')
            ->willReturn($userId);

        $userMock->expects($this->once())
            ->method('getRoles')
            ->willReturn($userRoles);

        $this->refreshTokenRepositoryMock
            ->method('save')
            ->willReturnCallback(function(RefreshToken $refreshToken) use ($refreshTokenId) {
                $ref = new \ReflectionClass($refreshToken);
                $prop = $ref->getProperty('id');
                $prop->setAccessible(true);
                $prop->setValue($refreshToken, $refreshTokenId);
            });

        $this->jwtEncoderMock->expects($this->once())
            ->method('encode')
            ->willReturn($tokenMock);

        $refreshToken = $this->authService->createUserRefreshToken($userMock);


        $this->assertInstanceOf(RefreshToken::class, $refreshToken);
        $this->assertSame($userMock, $refreshToken->getAppUser());
        $this->assertEquals($tokenMock, $refreshToken->getValue());
        $this->assertInstanceOf(\DateTime::class, $refreshToken->getExpiresAt());
    }

    public function testRefreshUserToken(): void
    {
        $userMock = $this->createMock(User::class);
        $userId = 1;
        $userRoles = ['USER'];
        $refreshTokenId = 1;
        $tokenMock = 'tokenMock';
        $newTokenMock = 'newTokenMock';
        $refreshTokenMock = $this->createMock(RefreshToken::class);

        $this->jwtEncoderMock->expects($this->once())
            ->method('decode')
            ->with($tokenMock)
            ->willReturn([
                'id' => $userId,
                'refresh_token_id' => $refreshTokenId,
            ]);

        $this->refreshTokenRepositoryMock->expects(($this->once()))
            ->method('find')
            ->with($refreshTokenId)
            ->willReturn($refreshTokenMock);

        $refreshTokenMock
            ->method('getAppUser')
            ->willReturn($userMock);
        
        $userMock
            ->method('getId')
            ->willReturn($userId);

        $getRefreshTokenValueCallNr = 0;
        $refreshTokenMock
            ->method('getValue')
            ->willReturnCallback(function() use (&$getRefreshTokenValueCallNr,$tokenMock, $newTokenMock){
                $getRefreshTokenValueCallNr++;
                return $getRefreshTokenValueCallNr == 1 ? $tokenMock : $newTokenMock;
            });

        $refreshTokenMock
            ->method('getId')
            ->willReturn($refreshTokenId);
        
        $userMock
            ->method('getRoles')
            ->willReturn($userRoles);  

        $this->jwtEncoderMock->expects($this->once())
            ->method('encode')
            ->willReturn($newTokenMock);

        $this->refreshTokenRepositoryMock->expects($this->once())
            ->method('save')
            ->with($refreshTokenMock, true);


        $refreshToken = $this->authService->refreshUserToken($tokenMock);

        $this->assertInstanceOf(RefreshToken::class, $refreshToken);
        $this->assertSame($userMock, $refreshToken->getAppUser());
        $this->assertSame($refreshTokenMock, $refreshToken);
        $this->assertEquals($newTokenMock, $refreshToken->getValue());
    }

    public function testRefreshUserTokenDecodeFailure(): void
    {
        $tokenMock = 'tokenMock';

        $this->jwtEncoderMock->expects($this->once())
            ->method('decode')
            ->with($tokenMock)
            ->willThrowException(new JWTDecodeFailureException('mockReason', 'mockMessage'));

        $this->expectException(TokenRefreshFailedException::class);

        $this->authService->refreshUserToken($tokenMock);
    }

    public function testRefreshUserTokenWithMissingUserIdInPayload(): void
    {
        $refreshTokenId = 1;
        $tokenMock = 'tokenMock';

        $this->jwtEncoderMock->expects($this->once())
            ->method('decode')
            ->with($tokenMock)
            ->willReturn([
                'refresh_token_id' => $refreshTokenId,
            ]);

        $this->expectException(TokenRefreshFailedException::class);

        $this->authService->refreshUserToken($tokenMock);
    }

    public function testRefreshUserTokenWithMissingRefreshTokenIdInPayload(): void
    {
        $userId = 1;
        $tokenMock = 'tokenMock';

        $this->jwtEncoderMock->expects($this->once())
            ->method('decode')
            ->with($tokenMock)
            ->willReturn([
                'id' => $userId,
            ]);

        $this->expectException(TokenRefreshFailedException::class);

        $this->authService->refreshUserToken($tokenMock);
    }

    public function testRefreshUserTokenWithCompromisedToken(): void
    {
        $userMock = $this->createMock(User::class);
        $userId = 1;
        $refreshTokenId = 1;
        $oldTokenMock = 'oldTokenMock';
        $actualTokenMock = 'actualTokenMock';
        $refreshTokenMock = $this->createMock(RefreshToken::class);

        $this->jwtEncoderMock->expects($this->once())
            ->method('decode')
            ->with($oldTokenMock)
            ->willReturn([
                'id' => $userId,
                'refresh_token_id' => $refreshTokenId,
            ]);

        $this->refreshTokenRepositoryMock->expects(($this->once()))
            ->method('find')
            ->with($refreshTokenId)
            ->willReturn($refreshTokenMock);

        $refreshTokenMock
            ->method('getAppUser')
            ->willReturn($userMock);
        
        $userMock
            ->method('getId')
            ->willReturn($userId);

        $refreshTokenMock
            ->method('getValue')
            ->willReturn($actualTokenMock);

        $this->refreshTokenRepositoryMock->expects($this->once())
            ->method('removeAllUserRefreshTokens')
            ->with($userMock);

        $this->expectException(TokenRefreshFailedException::class);

        $this->authService->refreshUserToken($oldTokenMock);
    }

    public function testRefreshTokenUsedByCurrentUser(): void
    {
        $userId = 1;
        $refreshTokenId = 1;
        $tokenMock = 'tokenMock';
        $refreshTokenMock = $this->createMock(RefreshToken::class);
        $postAuthTokenMock = $this->createMock(JWTPostAuthenticationToken::class);

        $this->securityMock->expects($this->once())
            ->method('getToken')
            ->willReturn($postAuthTokenMock);
        
        $postAuthTokenMock->expects($this->once())
            ->method('getCredentials')
            ->willReturn($tokenMock);

        $this->jwtEncoderMock->expects($this->once())
            ->method('decode')
            ->with($tokenMock)
            ->willReturn([
                'id' => $userId,
                'refresh_token_id' => $refreshTokenId,
            ]);

        $this->refreshTokenRepositoryMock->expects(($this->once()))
            ->method('find')
            ->with($refreshTokenId)
            ->willReturn($refreshTokenMock);


        $refreshToken = $this->authService->getRefreshTokenUsedByCurrentUser();

        $this->assertEquals($refreshTokenMock, $refreshToken);
    }

    public function testRefreshTokenUsedByCurrentUserWhenUnsupportedTokenUsed(): void
    {
        $postAuthTokenMock = $this->createMock(TokenInterface::class);

        $this->securityMock->expects($this->once())
            ->method('getToken')
            ->willReturn($postAuthTokenMock);
        
        $this->expectException(InvalidObjectException::class);

        $this->authService->getRefreshTokenUsedByCurrentUser();
    }

    public function testRefreshTokenUsedByCurrentUserWhenNoRefreshTokenIdInTokenPayload(): void
    {
        $tokenMock = 'tokenMock';
        $postAuthTokenMock = $this->createMock(JWTPostAuthenticationToken::class);

        $this->securityMock->expects($this->once())
            ->method('getToken')
            ->willReturn($postAuthTokenMock);
        
        $postAuthTokenMock->expects($this->once())
            ->method('getCredentials')
            ->willReturn($tokenMock);

        $this->jwtEncoderMock->expects($this->once())
            ->method('decode')
            ->with($tokenMock)
            ->willReturn([]);

        $refreshToken = $this->authService->getRefreshTokenUsedByCurrentUser();

        $this->assertNull($refreshToken);
    }

    public function testLogoutCurrentUser(): void
    {
        $userId = 1;
        $refreshTokenId = 1;
        $tokenMock = 'tokenMock';
        $refreshTokenMock = $this->createMock(RefreshToken::class);
        $postAuthTokenMock = $this->createMock(JWTPostAuthenticationToken::class);

        $this->securityMock->expects($this->once())
            ->method('getToken')
            ->willReturn($postAuthTokenMock);
        
        $postAuthTokenMock->expects($this->once())
            ->method('getCredentials')
            ->willReturn($tokenMock);

        $this->jwtEncoderMock->expects($this->once())
            ->method('decode')
            ->with($tokenMock)
            ->willReturn([
                'id' => $userId,
                'refresh_token_id' => $refreshTokenId,
            ]);

        $this->refreshTokenRepositoryMock->expects(($this->once()))
            ->method('find')
            ->with($refreshTokenId)
            ->willReturn($refreshTokenMock);

        $this->refreshTokenRepositoryMock->expects(($this->once()))
            ->method('remove')
            ->with($refreshTokenMock);

        $this->authService->logoutCurrentUser(new AuthLogoutDTO(false));
    }

    public function testLogoutCurrentUserWithOtherSessions(): void
    {
        $userId = 1;
        $refreshTokenId = 1;
        $tokenMock = 'tokenMock';
        $refreshTokenMock = $this->createMock(RefreshToken::class);
        $postAuthTokenMock = $this->createMock(JWTPostAuthenticationToken::class);
        $userMock = $this->createMock(User::class);

        $this->securityMock->expects($this->once())
            ->method('getUser')
            ->willReturn($userMock);

        $this->securityMock->expects($this->once())
            ->method('getToken')
            ->willReturn($postAuthTokenMock);
        
        $postAuthTokenMock->expects($this->once())
            ->method('getCredentials')
            ->willReturn($tokenMock);

        $this->jwtEncoderMock->expects($this->once())
            ->method('decode')
            ->with($tokenMock)
            ->willReturn([
                'id' => $userId,
                'refresh_token_id' => $refreshTokenId,
            ]);

        $this->refreshTokenRepositoryMock->expects(($this->once()))
            ->method('find')
            ->with($refreshTokenId)
            ->willReturn($refreshTokenMock);

        $this->refreshTokenRepositoryMock->expects(($this->once()))
            ->method('removeAllUserRefreshTokens')
            ->with($userMock);

        $this->authService->logoutCurrentUser(new AuthLogoutDTO(true));
    }
}
