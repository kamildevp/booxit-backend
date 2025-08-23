<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\EmailConfirmation;

use App\Entity\EmailConfirmation;
use App\Entity\User;
use App\Service\EmailConfirmation\EmailConfirmationHandler;
use App\Service\EmailConfirmation\Exception\ResolveVerificationHandlerException;
use DateTime;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\UriSigner;

class EmailConfirmationHandlerTest extends TestCase
{
    private MockObject&UriSigner $uriSignerMock;
    private EmailConfirmationHandler $emailConfirmationHandler;
    private string $secret = 'secret';
    private string $verificationHandler = 'MAIN';
    private string $baseUrl = 'https://main.com';

    protected function setUp(): void
    {
        $_ENV['VERIFICATION_HANDLER_MAIN'] = $this->baseUrl;
        $this->uriSignerMock = $this->createMock(UriSigner::class);
        $this->emailConfirmationHandler = new EmailConfirmationHandler($this->uriSignerMock, $this->secret);
    }

    public function testGenerateSignatureReturnsSignedUrl(): void
    {
        $userMock = $this->createMock(User::class);
        $emailConfirmationMock = $this->createMock(EmailConfirmation::class);
        $userId = 1;
        $emailConfirmationId = 1;
        $email = 'user@example.com';
        $expiryDate = new DateTime('+1 day');
        $encodedData = json_encode([$userId, $email]);
        $token = base64_encode(hash_hmac('sha256', $encodedData, $this->secret, true));
        $signedUrlMock = 'signedUrl';

        $userMock->method('getId')->willReturn($userId);
        $emailConfirmationMock->method('getCreator')->willReturn($userMock);
        $emailConfirmationMock->method('getId')->willReturn($emailConfirmationId);
        $emailConfirmationMock->method('getEmail')->willReturn($email);
        $emailConfirmationMock->method('getExpiryDate')->willReturn($expiryDate);
        $emailConfirmationMock->method('getVerificationHandler')->willReturn($this->verificationHandler);

        $url = $this->baseUrl . '?' . http_build_query([
            'id' => $userId,
            'token' => $token,
            'expires' => $expiryDate->getTimestamp()
        ]);

        $this->uriSignerMock->expects($this->once())
            ->method('sign')
            ->with($url)
            ->willReturn($signedUrlMock);

        $result = $this->emailConfirmationHandler->generateSignedUrl($emailConfirmationMock, []);
        $this->assertEquals($signedUrlMock, $result);
    }

    public function testValidateEmailConfirmationReturnsTrueForValidEmailConfirmation(): void
    {
        $userMock = $this->createMock(User::class);
        $emailConfirmationMock = $this->createMock(EmailConfirmation::class);
        $userId = 1;
        $emailConfirmationId = 1;
        $email = 'user@example.com';
        $expiryDate = new DateTime('+1 day');
        $encodedData = json_encode([$userId, $email]);
        $token = base64_encode(hash_hmac('sha256', $encodedData, $this->secret, true));
        $signatureMock = 'signature';
        $typeMock = 'type';

        $userMock->method('getId')->willReturn($userId);
        $emailConfirmationMock->method('getCreator')->willReturn($userMock);
        $emailConfirmationMock->method('getId')->willReturn($emailConfirmationId);
        $emailConfirmationMock->method('getEmail')->willReturn($email);
        $emailConfirmationMock->method('getExpiryDate')->willReturn($expiryDate);
        $emailConfirmationMock->method('getType')->willReturn($typeMock);
        $emailConfirmationMock->method('getVerificationHandler')->willReturn($this->verificationHandler);

        $url = $this->baseUrl . '?' . http_build_query([
            '_hash' => $signatureMock,
            'expires' => $expiryDate->getTimestamp(),
            'id' => $userId,
            'token' => $token,
        ]);

        $this->uriSignerMock->expects($this->once())
            ->method('check')
            ->with($url)
            ->willReturn(true);

        $result = $this->emailConfirmationHandler->validateEmailConfirmation(
            $emailConfirmationMock, 
            $token, 
            $signatureMock, 
            $expiryDate->getTimestamp(),
            $typeMock
        );

        $this->assertTrue($result);
    }

    public function testValidateEmailConfirmationReturnsFalseForInvalidEmailConfirmation(): void
    {
        $emailConfirmationMock = $this->createMock(EmailConfirmation::class);
        $userId = 1;
        $emailConfirmationId = 1;
        $email = 'user@example.com';
        $expiryDate = new DateTime('+1 day');
        $encodedData = json_encode([$userId, $email]);
        $token = base64_encode(hash_hmac('sha256', $encodedData, $this->secret, true));
        $signatureMock = 'signature';
        $typeMock = 'type';

        $emailConfirmationMock->method('getId')->willReturn($emailConfirmationId);
        $emailConfirmationMock->method('getVerificationHandler')->willReturn($this->verificationHandler);

        $url = $this->baseUrl . '?' . http_build_query([
            '_hash' => $signatureMock,
            'expires' => $expiryDate->getTimestamp(),
            'id' => $userId,
            'token' => $token,
        ]);

        $this->uriSignerMock->expects($this->once())
            ->method('check')
            ->with($url)
            ->willReturn(false);

        $result = $this->emailConfirmationHandler->validateEmailConfirmation(
            $emailConfirmationMock, 
            $token, 
            $signatureMock, 
            $expiryDate->getTimestamp(),
            $typeMock
        );

        $this->assertFalse($result);
    }

    public function testResolveVerificationHandlerUrlReturnsUrlForDefinedVerificationHandler(): void
    {
        $url = $this->emailConfirmationHandler->resolveVerificationHandlerUrl($this->verificationHandler);
        $this->assertEquals($this->baseUrl, $url);
    }

    public function testResolveVerificationHandlerUrlThrowsExceptionForUndefinedVerificationHandler(): void
    {
        $this->expectException(ResolveVerificationHandlerException::class);
        $this->emailConfirmationHandler->resolveVerificationHandlerUrl('INVALID');
    }
}
