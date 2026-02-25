<?php

declare(strict_types=1);

namespace App\Service\Auth\Social;

use App\Entity\User;
use App\Enum\Auth\Social\SocialAuthProvider;
use App\Enum\TranslationsLocale;
use App\Repository\UserRepository;
use App\Service\Auth\Social\DTO\SocialOwnerDTO;
use App\Service\Auth\Social\Exception\ResolveAuthHandlerRedirectUrlException;
use App\Service\Auth\Social\Exception\SocialAuthFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class AbstractProvider
{
    const AUTH_HANDLER_VAR_SUFFIX = 'AUTH_HANDLER_REDIRECT_URL';

    public function __construct(
        protected UserRepository $userRepository, 
        protected ValidatorInterface $validator
    )
    {
        
    }

    public function resolveAuthHandlerRedirectUrl(string $authHandler, SocialAuthProvider $provider): string
    {
        $envVarName = join('_', [
            strtoupper($authHandler),
            $provider->value,
            self::AUTH_HANDLER_VAR_SUFFIX,
        ]);
        
        if(!array_key_exists($envVarName, $_ENV)){
            throw new ResolveAuthHandlerRedirectUrlException("Redirect url for auth handler is not defined");
        }

        return $_ENV[$envVarName];
    }

    protected function resolveUser(
        SocialAuthProvider $authProvider,
        string $email,
        string $name,
        ?string $locale,
        ?string $authProviderUserId,
    ): User
    {
        $ownerDTO = $this->parseOwnerInfo(
            $email,
            $name,
            $locale,
            $authProviderUserId
        );

        $user = $this->userRepository->findOneByFieldValue('email', $ownerDTO->email, disableFilters: ['softdeleteable', 'verifiable']);
        if(!$user instanceof User){
            return $this->createUser($ownerDTO, $authProvider);
        }

        if($user->isDeleted()){
            throw new SocialAuthFailedException('User account has been deleted');
        }
        else if(!$user->isVerified()){
            $user->setVerified(true);
            $this->userRepository->save($user, true);
        }

        return $user;
    }

    protected function createUser(
        SocialOwnerDTO $ownerDTO,
        SocialAuthProvider $authProvider,
    ): User
    {
        $user = new User();
        $user->setEmail($ownerDTO->email);
        $user->setName($ownerDTO->name);
        $user->setAuthProvider($authProvider->value);
        $user->setAuthProviderUserId($ownerDTO->id);
        $user->setLanguagePreference($this->resolveUserLocale($ownerDTO->locale)->value);
        $user->setVerified(true);
        $this->userRepository->save($user, true);

        return $user;   
    }

    protected function resolveUserLocale(?string $providerLocale): TranslationsLocale
    {
        if(empty($providerLocale)){
            return TranslationsLocale::EN;
        }

        $locales = TranslationsLocale::values();
        $matchingLocales = array_filter($locales, fn($locale) => preg_match('/^' . preg_quote($locale) . '(\-)?/', strtolower($providerLocale)));
        return !empty($matchingLocales) ? TranslationsLocale::from(reset($matchingLocales)) : TranslationsLocale::EN;
    }

    protected function parseOwnerInfo(        
        string $email,
        string $name,
        ?string $locale,
        ?string $authProviderUserId
    ): SocialOwnerDTO
    {
        $dto = new SocialOwnerDTO(
            $email,
            mb_substr($name, 0, 50),
            $locale,
            $authProviderUserId,
        );
        
        $errors = $this->validator->validate($dto);

        if(count($errors) > 0){
            throw new SocialAuthFailedException('Received invalid data from auth provider');
        }

        return $dto;
    }
}