<?php

declare(strict_types=1);

namespace App\Security\DTO;

use App\DTO\AbstractDTO;
use App\Enum\Auth\Social\SocialAuthProvider;
use App\Validator\Constraints as CustomAssert;
use Symfony\Component\Validator\Constraints as Assert;

class GoogleAuthDTO extends AbstractDTO implements SocialAuthDTOInterface
{
    public function __construct(
        #[CustomAssert\DefinedSocialAuthHandler(SocialAuthProvider::GOOGLE)]
        private string $authHandler,
        #[Assert\NotBlank]
        private string $code,
        #[Assert\NotBlank]
        private string $codeVerifier,
    )
    {

    }

    public function getAuthHandler(): string
    {
        return $this->authHandler;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getCodeVerifier(): string
    {
        return $this->codeVerifier;
    }
}