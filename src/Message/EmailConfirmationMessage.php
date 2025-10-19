<?php

declare(strict_types=1);

namespace App\Message;

class EmailConfirmationMessage extends EmailMessage
{
    public function __construct(
        private int $emailConfirmationId,
        string $emailType,
        string $email,
        array $templateParams
    ) 
    {
        parent::__construct($emailType, $email, $templateParams);
    }

    public function getEmailConfirmationId(): int
    {
        return $this->emailConfirmationId;
    }
}