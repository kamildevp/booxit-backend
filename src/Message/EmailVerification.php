<?php

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