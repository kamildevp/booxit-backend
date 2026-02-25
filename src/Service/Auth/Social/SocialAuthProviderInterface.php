<?php

declare(strict_types=1);

namespace App\Service\Auth\Social;

use App\Entity\User;

interface SocialAuthProviderInterface
{
    public function getUser(string $authHandler, string $code, string $pkceCode): User;
}