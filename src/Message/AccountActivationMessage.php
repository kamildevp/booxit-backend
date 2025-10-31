<?php

declare(strict_types=1);

namespace App\Message;

class AccountActivationMessage extends EmailMessage
{
    public function __construct(
        private int $emailConfirmationId,
        private int $userId,
        string $emailType,
        string $email,
        array $templateParams,
        string $locale = 'en',
    ) 
    {
        parent::__construct($emailType, $email, $templateParams, $locale);
    }

    public function getEmailConfirmationId(): int
    {
        return $this->emailConfirmationId;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }
}