<?php

declare(strict_types=1);

namespace App\Service\EmailConfirmation;

use App\Entity\EmailConfirmation;

interface EmailConfirmationHandlerInterface
{
    public function generateSignedUrl(EmailConfirmation $emailConfirmation, array $extraParams = []): string;

    public function validateEmailConfirmation(
        EmailConfirmation $emailConfirmation, 
        string $token, 
        string $signature, 
        int $expiryTimestamp,
        string $type
    ): bool;

    public function resolveVerificationHandlerUrl(string $verificationHandler): string;
}
