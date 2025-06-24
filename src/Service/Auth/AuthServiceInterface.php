<?php

namespace App\Service\Auth;

use App\Entity\RefreshToken;
use App\Entity\User;

interface AuthServiceInterface
{
    public function createUserRefreshToken(User $user): RefreshToken;

    public function refreshUserToken(string $refreshTokenValue): RefreshToken;

    public function getRefreshTokenUsedByCurrentUser(): ?RefreshToken;
}