<?php

declare(strict_types=1);

namespace App\Security;

use App\Response\UnauthorizedResponse;
use App\Security\DTO\SocialAuthDTOInterface;
use App\Service\Auth\Social\Exception\SocialAuthFailedException;
use App\Service\Auth\Social\SocialAuthProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\Authenticator\Token\PostAuthenticationToken;

abstract class SocialAuthenticator extends AbstractAuthenticator
{
    
    public function __construct(
        private SocialAuthProviderInterface $provider,
        private AuthenticationSuccessHandler $authSuccessHandler,
    )
    {
        
    }

    public function supports(Request $request): ?bool
    {
        return true;
    }

    public function socialAuthenticate(SocialAuthDTOInterface $dto): Passport
    {
        try{
            $user = $this->provider->getUser($dto->getAuthHandler(), $dto->getCode(), $dto->getCodeVerifier());
        }
        catch(SocialAuthFailedException)
        {
            throw new CustomUserMessageAuthenticationException('Invalid credentials');
        }

        $passport = new SelfValidatingPassport(new UserBadge((string)$user->getId(), function() use ($user){
            return $user;
        }));

        return $passport;
    }

    public function createToken(Passport $passport, string $firewallName): TokenInterface
    {
        return new PostAuthenticationToken(
            $passport->getUser(),
            $firewallName,
            $passport->getUser()->getRoles()
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