<?php

declare(strict_types=1);

namespace App\DTO\User;

use App\DTO\AbstractDTO;
use App\DTO\EmailConfirmation\Trait\VerificationHandlerFieldDTO;
use App\Validator\Constraints\Compound as Compound;

class UserResetPasswordRequestDTO extends AbstractDTO 
{
    use VerificationHandlerFieldDTO;

    #[Compound\EmailRequirements]
    public readonly string $email;

    public function __construct(string $email, string $verificationHandler)
    {
        $this->email = $email;
        $this->verificationHandler = $verificationHandler;
    }
}