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
    public function resolveUserWrapper(
        string $email,
        string $name,
        ?string $locale,
        ?string $authProviderUserId,
    ): User
    {
        return parent::resolveUser($email, $name, $locale, $authProviderUserId);
    }

    public function createUserWrapper(SocialOwnerDTO $ownerDTO): User
    {
        return parent::createUser($ownerDTO);
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

    public function getProviderType(): SocialAuthProvider
    {
        return SocialAuthProvider::GOOGLE;
    }
}
