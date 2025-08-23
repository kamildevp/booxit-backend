<?php

declare(strict_types=1);

namespace App\Security;

use App\Model\PostAuthRefreshToken;
use App\Response\AuthSuccessResponse;
use App\Service\Auth\AuthServiceInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;


class AuthenticationSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    public function __construct(
        private AuthServiceInterface $authService,
        private JWTTokenManagerInterface $jwtManager
    )
    {

    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): ?Response
    {
        $user = $token->getUser();

        if($token instanceof PostAuthRefreshToken){
            $refreshTokenId = $token->getRefreshTokenId();
            $refreshTokenValue = $token->getRefreshTokenValue();
        }
        else{
            $refreshToken = $this->authService->createUserRefreshToken($user);
            $refreshTokenId = $refreshToken->getId();
            $refreshTokenValue = $refreshToken->getValue();
        }

        $accessTokenValue = $this->jwtManager->createFromPayload($user, [
            'refresh_token_id' => $refreshTokenId
        ]);

        return new AuthSuccessResponse($accessTokenValue, $refreshTokenValue);
    }
}
