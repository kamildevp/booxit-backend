<?php

declare(strict_types=1);

namespace App\Service\Auth\Social;

use App\Entity\User;
use App\Enum\Auth\Social\SocialAuthProvider;

interface SocialAuthProviderInterface
{
    public function getUser(string $authHandler, string $code, string $pkceCode): User;

    public function getProviderType(): SocialAuthProvider;

    public function resolveAuthHandlerRedirectUrl(string $authHandler): string;
}