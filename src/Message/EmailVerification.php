<?php

namespace App\Message;

class EmailVerification
{
    public function __construct(
        private int $userId,
        private bool $removeUserOnFail = false
    ) {
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function shouldUserBeRemovedOnFailure(): bool
    {
        return $this->removeUserOnFail;
    }
}