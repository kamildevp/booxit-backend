<?php

declare(strict_types=1);

namespace App\Security;

use App\Security\DTO\GoogleAuthDTO;
use App\Service\Auth\Social\GoogleProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class GoogleAuthenticator extends SocialAuthenticator
{
    
    public function __construct(
        private ValidatorInterface $validator,
        GoogleProvider $provider,
        AuthenticationSuccessHandler $authSuccessHandler,
    )
    {
        parent::__construct($provider, $authSuccessHandler);
    }


    public function supports(Request $request): ?bool
    {
        return true;
    }

    public function authenticate(Request $request): Passport
    {
        $requestData = $request->toArray();
        $dto = new GoogleAuthDTO(
            $requestData['auth_handler'] ?? '',
            $requestData['code'] ?? '',
            $requestData['code_verifier'] ?? '',
        );

        $errors = $this->validator->validate($dto);
        if(count($errors) > 0){
            throw new CustomUserMessageAuthenticationException('Invalid auth parameters');
        }

        return parent::socialAuthenticate($dto);
    }
}