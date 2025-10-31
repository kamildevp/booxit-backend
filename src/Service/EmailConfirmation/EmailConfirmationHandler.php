<?php

declare(strict_types=1);

namespace App\Service\EmailConfirmation;

use App\Entity\EmailConfirmation;
use App\Service\EmailConfirmation\Exception\ResolveVerificationHandlerException;
use Symfony\Component\HttpFoundation\UriSigner;

class EmailConfirmationHandler implements EmailConfirmationHandlerInterface
{
    const VERIFICATION_HANDLER_VAR_PREFIX = 'VERIFICATION_HANDLER_';

    public function __construct(
        private UriSigner $uriSigner,
        #[\SensitiveParameter]private string $secret
    )
    {}

    public function generateSignedUrl(EmailConfirmation $emailConfirmation, array $extraParams = []): string
    {
        $extraParams['id'] = $emailConfirmation->getId();
        $extraParams['token'] = $this->createToken($emailConfirmation->getId(), $emailConfirmation->getEmail());
        $extraParams['expires'] = $emailConfirmation->getExpiryDate()->getTimestamp();
        $extraParams['type'] = $emailConfirmation->getType();
        $baseUrl = $this->resolveVerificationHandlerUrl($emailConfirmation->getVerificationHandler());

        $url = $baseUrl . '?' . http_build_query($extraParams);

        return $this->uriSigner->sign($url);
    }

    public function validateEmailConfirmation(
        EmailConfirmation $emailConfirmation, 
        string $token, 
        string $signature, 
        int $expiryTimestamp,
        string $type
    ): bool
    {
        $baseUrl = $this->resolveVerificationHandlerUrl($emailConfirmation->getVerificationHandler());
        $params = [
            '_hash' => $signature, 
            'expires' => $expiryTimestamp,
            'id' => $emailConfirmation->getId(),
            'token' => $token,
            'type' => $type
        ];

        $signedUrl = $baseUrl . '?' . http_build_query($params);
        
        if (!$this->uriSigner->check($signedUrl)) {
            return false;
        }

        $emailConfirmationExpiryTimestamp = $emailConfirmation->getExpiryDate()->getTimestamp();
        if (
            $emailConfirmation->getType() != $type ||
            $expiryTimestamp != $emailConfirmationExpiryTimestamp || 
            $emailConfirmationExpiryTimestamp <= time()
        ) {
            return false;
        }

        $validToken = $this->createToken($emailConfirmation->getId(), $emailConfirmation->getEmail());

        if (!hash_equals($validToken, $token)) {
            return false;
        }

        return true;
    }

    private function createToken(int $emailConfirmationId, string $email): string
    {
        $encodedData = json_encode([$emailConfirmationId, $email]);

        return base64_encode(hash_hmac('sha256', $encodedData, $this->secret, true));
    }

    
    public function resolveVerificationHandlerUrl(string $verificationHandler): string
    {
        $envVarName = self::VERIFICATION_HANDLER_VAR_PREFIX . strtoupper($verificationHandler);
        
        if(!array_key_exists($envVarName, $_ENV)){
            throw new ResolveVerificationHandlerException('Verification Handler is not defined');
        }

        return $_ENV[$envVarName];
    }
}
