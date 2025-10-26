<?php

declare(strict_types=1);

namespace App\DTO\User;

use App\DTO\AbstractDTO;
use App\DTO\EmailConfirmation\Trait\VerificationHandlerFieldDTO;
use App\DTO\Trait\LanguagePreferenceFieldDTO;
use App\DTO\User\Trait\UserPasswordFieldDTO;
use App\Entity\User;
use App\Validator\Constraints as CustomAssert;
use App\Validator\Constraints\Compound as Compound;
use OpenApi\Attributes as OA;

class UserCreateDTO extends AbstractDTO 
{
    use UserPasswordFieldDTO, VerificationHandlerFieldDTO, LanguagePreferenceFieldDTO;

    public function __construct(
        #[Compound\NameRequirements]
        public readonly string $name,
        #[OA\Property(format: 'email')]
        #[Compound\EmailRequirements]
        #[CustomAssert\UniqueEntityField(User::class, 'email')]
        public readonly string $email,
        #[Compound\UsernameRequirements]
        #[CustomAssert\UniqueEntityField(User::class, 'username')]
        public readonly string $username,
        string $verificationHandler, 
        string $password,
        string $languagePreference,
    )
    {
        $this->verificationHandler = $verificationHandler;
        $this->password = $password;
        $this->languagePreference = $languagePreference;
    }
}