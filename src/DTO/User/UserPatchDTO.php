<?php

declare(strict_types=1);

namespace App\DTO\User;

use App\DTO\AbstractDTO;
use App\DTO\EmailConfirmation\Trait\VerificationHandlerFieldDTO;
use App\DTO\Trait\LanguagePreferenceFieldDTO;
use App\DTO\User\Trait\UserBaseFieldsDTO;

class UserPatchDTO extends AbstractDTO 
{
    use UserBaseFieldsDTO, VerificationHandlerFieldDTO, LanguagePreferenceFieldDTO;

    public function __construct(
        string $name, 
        string $email, 
        string $verificationHandler,
        string $languagePreference,
    )
    {
        $this->name = $name;
        $this->email = $email;
        $this->verificationHandler = $verificationHandler;
        $this->languagePreference = $languagePreference;
    }
}