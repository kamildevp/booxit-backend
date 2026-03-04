<?php

declare(strict_types=1);

namespace App\Service\Auth\Social;

use App\Entity\User;
use App\Enum\Auth\Social\SocialAuthProvider;
use App\Repository\UserRepository;
use App\Service\Auth\Social\Exception\SocialAuthFailedException;
use Exception;
use League\OAuth2\Client\Provider\Google;
use League\OAuth2\Client\Provider\GoogleUser;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class GoogleProvider extends AbstractProvider implements SocialAuthProviderInterface
{
    public function __construct(
        private Google $provider,
        UserRepository $userRepository,
        ValidatorInterface $validator,
    )
    {
        parent::__construct($userRepository, $validator);
    }

    public function getUser(string $authHandler, string $code, string $pkceCode): User
    {
        $owner = $this->fetchGoogleUser($authHandler, $code, $pkceCode);
        return $this->resolveUser(
            $owner->getEmail() ?? '',
            $owner->getName(),
            $owner->getLocale(),
            $owner->getId(),
        );
    }

    private function fetchGoogleUser(string $authHandler, string $code, string $pkceCode): GoogleUser
    {
        $redirectUri = $this->resolveAuthHandlerRedirectUrl($authHandler);
        try{
            $token = $this->provider->getAccessToken('authorization_code', [
                'code' => $code,
                'code_verifier' => $pkceCode,
                'redirect_uri' => $redirectUri
            ]);

            /** @var GoogleUser */
            $owner = $this->provider->getResourceOwner($token);
        }
        catch(Exception){
            throw new SocialAuthFailedException('Google authentication failed');
        }
        
        if(!$owner->isEmailTrustworthy()){
            throw new SocialAuthFailedException('Owner email is not trustworthy');
        }

        return $owner;
    }

    public function getProviderType(): SocialAuthProvider
    {
        return SocialAuthProvider::GOOGLE;
    }
}