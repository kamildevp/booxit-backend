<?php

declare(strict_types=1);

namespace App\Security;

use App\Exceptions\TokenRefreshFailedException;
use App\Model\PostAuthRefreshToken;
use App\Response\UnauthorizedResponse;
use App\Service\Auth\AuthServiceInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class RefreshTokenAuthenticator extends AbstractAuthenticator
{
    
    public function __construct(
        private AuthServiceInterface $authService,
        private AuthenticationSuccessHandler $authSuccessHandler
    )
    {
        
    }


    public function supports(Request $request): ?bool
    {
        return true;
    }

    public function authenticate(Request $request): Passport
    {
        $requestParams = $request->toArray();
        if (!array_key_exists('refresh_token', $requestParams) || empty($requestParams['refresh_token'])) {
            throw new CustomUserMessageAuthenticationException('Missing refresh token');
        }

        $refreshTokenValue = $requestParams['refresh_token'];

        try{
            $refreshToken = $this->authService->refreshUserToken($refreshTokenValue);
        }
        catch(TokenRefreshFailedException)
        {
            throw new CustomUserMessageAuthenticationException('Invalid or expired refresh token');
        }
        
        $user = $refreshToken->getAppUser();
        $passport = new SelfValidatingPassport(new UserBadge((string)$user->getId(), function() use ($user){
            return $user;
        }));

        $passport->setAttribute('refresh_token', $refreshToken->getValue());
        $passport->setAttribute('refresh_token_id', $refreshToken->getId());

        return $passport;
    }

    public function createToken(Passport $passport, string $firewallName): TokenInterface
    {
        $refreshTokenValue = $passport->getAttribute('refresh_token');
        $refreshTokenId = $passport->getAttribute('refresh_token_id');

        return new PostAuthRefreshToken(
            $passport->getUser(),
            $firewallName,
            $passport->getUser()->getRoles(),
            $refreshTokenValue,
            $refreshTokenId
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return $this->authSuccessHandler->onAuthenticationSuccess($request, $token);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new UnauthorizedResponse($exception->getMessage());
    }
}