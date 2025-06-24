<?php

namespace App\DTO\User\Trait;

use App\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\Constraints as CustomAssert;

trait UserBaseFieldsDTO {
    #[Assert\Length(
        min: 6,
        max: 50,
        minMessage: 'Parameter must be at least {{ limit }} characters long',
        maxMessage: 'Parameter cannot be longer than {{ limit }} characters',
    )]
    public readonly string $name;

    #[Assert\Email(
        message: 'Value is not a valid email',
    )]
    #[CustomAssert\UniqueEntityField(User::class, 'email')]
    public readonly string $email;
}