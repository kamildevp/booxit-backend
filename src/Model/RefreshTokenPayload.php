<?php

namespace App\Model;

class RefreshTokenPayload
{
    public function __construct(private int $userId, private int $refreshTokenId)
    {
        
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
    }

    public function getRefreshTokenId(): int
    {
        return $this->refreshTokenId;
    }

    public function setRefreshTokenId(int $refreshTokenId): void
    {
        $this->refreshTokenId = $refreshTokenId;
    }
}
