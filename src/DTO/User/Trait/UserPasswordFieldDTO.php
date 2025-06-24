<?php

namespace App\DTO\User\Trait;

use Symfony\Component\Validator\Constraints as Assert;

trait UserPasswordFieldDTO {
    #[Assert\Regex(
        pattern: '/^(?=.*[A-Z])(?=.*\d)[A-Z\d!@#$%?&*]{8,}$/i',
        message: 'Password length must be from 8 to 20 characters, can contain special characters(!#$%?&*) and must have at least one letter and digit'
    )]
    public readonly string $password;
}