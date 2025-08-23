<?php

declare(strict_types=1);

namespace App\Message;

class EmailVerification
{
    public function __construct(
        private int $emailConfirmationId,
        private bool $removeUserOnFail = false
    ) {
    }

    public function getEmailConfirmationId(): int
    {
        return $this->emailConfirmationId;
    }

    public function shouldUserBeRemovedOnFailure(): bool
    {
        return $this->removeUserOnFail;
    }
}