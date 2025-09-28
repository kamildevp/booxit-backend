<?php

declare(strict_types=1);

namespace App\DTO\EmailConfirmation\Trait;

use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\Constraints as CustomAssert;
use OpenApi\Attributes as OA;

trait VerificationHandlerFieldDTO {
    #[OA\Property(enum: ['internal'])]
    #[Assert\NotBlank]
    #[CustomAssert\DefinedVerificationHandler]
    public readonly string $verificationHandler;
}