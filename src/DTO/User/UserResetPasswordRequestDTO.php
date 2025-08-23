<?php

declare(strict_types=1);

namespace App\DTO\User;

use App\DTO\AbstractDTO;
use App\DTO\EmailConfirmation\Trait\VerificationHandlerFieldDTO;
use Symfony\Component\Validator\Constraints as Assert;

class UserResetPasswordRequestDTO extends AbstractDTO 
{
    use VerificationHandlerFieldDTO;

    #[Assert\Email(
        message: 'Parameter is not a valid email',
    )]
    public readonly string $email;

    public function __construct(string $email, string $verificationHandler)
    {
        $this->email = $email;
        $this->verificationHandler = $verificationHandler;
    }
}