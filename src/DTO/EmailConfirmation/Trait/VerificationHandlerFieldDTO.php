<?php

declare(strict_types=1);

namespace App\DTO\EmailConfirmation\Trait;

use App\Validator\Constraints\Compound as Compound;
use OpenApi\Attributes as OA;

trait VerificationHandlerFieldDTO {
    #[OA\Property(enum: ['internal'])]
    #[Compound\VerificationHandlerRequirements]
    public readonly string $verificationHandler;
}