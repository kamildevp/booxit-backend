<?php

declare(strict_types=1);

namespace App\DTO\User;

use App\DTO\AbstractDTO;
use App\DTO\EmailConfirmation\Trait\VerificationHandlerFieldDTO;
use App\DTO\User\Trait\UserBaseFieldsDTO;

class UserPatchDTO extends AbstractDTO 
{
    use UserBaseFieldsDTO, VerificationHandlerFieldDTO;

    public function __construct(string $name, string $email, string $verificationHandler)
    {
        $this->name = $name;
        $this->email = $email;
        $this->verificationHandler = $verificationHandler;
    }
}