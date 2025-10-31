<?php

declare(strict_types=1);

namespace App\DTO\User\Trait;

use App\Validator\Constraints\Compound as Compound;
use OpenApi\Attributes as OA;

trait UserPasswordFieldDTO 
{
    #[OA\Property(example: 'password123')]
    #[Compound\PasswordRequirements]
    public readonly string $password;
}