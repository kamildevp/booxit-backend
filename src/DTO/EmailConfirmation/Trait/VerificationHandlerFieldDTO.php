<?php

namespace App\DTO\EmailConfirmation\Trait;

use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\Constraints as CustomAssert;
use OpenApi\Attributes as OA;

trait VerificationHandlerFieldDTO {
    #[OA\Property(enum: ['main'])]
    #[Assert\NotBlank]
    #[CustomAssert\DefinedVerificationHandler]
    public readonly string $verificationHandler;
}