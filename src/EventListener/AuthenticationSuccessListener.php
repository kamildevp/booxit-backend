<?php

namespace App\EventListener;

use App\Model\PostAuthRefreshToken;
use App\Response\AuthSuccessResponse;
use App\Service\Auth\AuthService;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Bundle\SecurityBundle\Security;

class AuthenticationSuccessListener
{
    public function __construct(private AuthService $authService, private Security $security)
    {
        
    }

    /**
     * @param AuthenticationSuccessEvent $event
     */
    public function onAuthenticationSuccessResponse(AuthenticationSuccessEvent $event)
    {
        $data = $event->getData();
        $user = $event->getUser();
        $token = $this->security->getToken();
        $accessToken = $data['token'];
        $refreshToken = $token instanceof PostAuthRefreshToken ? $token->getRefreshTokenValue() : $this->authService->createUserRefreshToken($user);
        
        $response = new AuthSuccessResponse($accessToken, $refreshToken);

        $event->setData($response->getRawData());
    }
}

