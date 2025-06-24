<?php

namespace App\Service\EmailConfirmation;

use App\Entity\EmailConfirmation;

interface EmailConfirmationHandlerInterface
{
    public function generateSignature(EmailConfirmation $emailConfirmation, array $extraParams = []): string;

    public function validateEmailConfirmation(
        EmailConfirmation $emailConfirmation, 
        string $token, 
        string $signature, 
        int $expiryTimestamp,
        string $type
    ): bool;

    public function createToken(string $userId, string $email): string;

    public function resolveVerificationHandlerUrl(string $verificationHandler): string;
}
