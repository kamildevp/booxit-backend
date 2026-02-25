<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Auth\Social;

use App\Entity\User;
use App\Enum\Auth\Social\SocialAuthProvider;
use App\Enum\TranslationsLocale;
use App\Service\Auth\Social\AbstractProvider;
use App\Service\Auth\Social\DTO\SocialOwnerDTO;

class TestProvider extends AbstractProvider
{
    public function resolveAuthHandlerRedirectUrlWrapper(string $authHandler, SocialAuthProvider $provider): string
    {
        return parent::resolveAuthHandlerRedirectUrl($authHandler, $provider);
    }

    public function resolveUserWrapper(
        SocialAuthProvider $authProvider,
        string $email,
        string $name,
        ?string $locale,
        ?string $authProviderUserId,
    ): User
    {
        return parent::resolveUser($authProvider, $email, $name, $locale, $authProviderUserId);
    }

    public function createUserWrapper(SocialOwnerDTO $ownerDTO, SocialAuthProvider $authProvider): User
    {
        return parent::createUser($ownerDTO, $authProvider);
    }

    public function resolveUserLocaleWrapper(?string $providerLocale): TranslationsLocale
    {
        return parent::resolveUserLocale($providerLocale);
    }

    public function parseOwnerInfoWrapper(        
        string $email,
        string $name,
        ?string $locale,
        ?string $authProviderUserId
    ): SocialOwnerDTO
    {
        return parent::parseOwnerInfo($email, $name, $locale, $authProviderUserId);
    }
}
