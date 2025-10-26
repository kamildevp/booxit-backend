<?php

declare(strict_types=1);

namespace App\DTO\User;

use App\DTO\AbstractDTO;
use App\DTO\EmailConfirmation\Trait\VerificationHandlerFieldDTO;
use App\DTO\Trait\LanguagePreferenceFieldDTO;
use App\Entity\User;
use App\Validator\Constraints as CustomAssert;
use App\Validator\Constraints\Compound as Compound;
use OpenApi\Attributes as OA;

class UserPatchDTO extends AbstractDTO 
{
    use VerificationHandlerFieldDTO, LanguagePreferenceFieldDTO;

    public function __construct(
        #[Compound\NameRequirements]
        public readonly string $name,
        #[OA\Property(format: 'email')]
        #[Compound\EmailRequirements]
        #[CustomAssert\UniqueEntityField(User::class, 'email', ['currentUser'])]
        public readonly string $email,
        #[Compound\UsernameRequirements]
        #[CustomAssert\UniqueEntityField(User::class, 'username', ['currentUser'])]
        public readonly string $username,
        string $verificationHandler,
        string $languagePreference,
    )
    {
        $this->verificationHandler = $verificationHandler;
        $this->languagePreference = $languagePreference;
    }
}