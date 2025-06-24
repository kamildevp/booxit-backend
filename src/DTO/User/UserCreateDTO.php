<?php

namespace App\DTO\User;

use App\DTO\AbstractDTO;
use App\DTO\EmailConfirmation\Trait\VerificationHandlerFieldDTO;
use App\DTO\User\Trait\UserBaseFieldsDTO;
use App\DTO\User\Trait\UserPasswordFieldDTO;

class UserCreateDTO extends AbstractDTO 
{
    use UserBaseFieldsDTO, UserPasswordFieldDTO, VerificationHandlerFieldDTO;

    public function __construct(string $name, string $email, string $verificationHandler, string $password)
    {
        $this->name = $name;
        $this->email = $email;
        $this->verificationHandler = $verificationHandler;
        $this->password = $password;
    }
}