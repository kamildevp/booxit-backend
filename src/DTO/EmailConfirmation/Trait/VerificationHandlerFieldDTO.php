<?php

namespace App\DTO\EmailConfirmation\Trait;

use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\Constraints as CustomAssert;

trait VerificationHandlerFieldDTO {
    #[Assert\NotBlank]
    #[CustomAssert\DefinedVerificationHandler]
    public readonly string $verificationHandler;
}