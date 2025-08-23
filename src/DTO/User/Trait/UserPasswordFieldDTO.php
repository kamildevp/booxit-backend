<?php

declare(strict_types=1);

namespace App\DTO\User\Trait;

use Symfony\Component\Validator\Constraints as Assert;
use OpenApi\Attributes as OA;

trait UserPasswordFieldDTO 
{
    #[OA\Property(example: 'password123')]
    #[Assert\Regex(
        pattern: '/^(?=.*[A-Z])(?=.*\d)[A-Z\d!@#$%?&*]{8,}$/i',
        message: 'Password length must be from 8 to 20 characters, can contain special characters(!#$%?&*) and must have at least one letter and digit'
    )]
    public readonly string $password;
}