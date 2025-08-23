<?php

declare(strict_types=1);

namespace App\DTO\User\Trait;

use App\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\Constraints as CustomAssert;
use OpenApi\Attributes as OA;

trait UserBaseFieldsDTO {
    #[Assert\Length(
        min: 6,
        max: 50,
        minMessage: 'Parameter must be at least {{ limit }} characters long',
        maxMessage: 'Parameter cannot be longer than {{ limit }} characters',
    )]
    public readonly string $name;

    #[OA\Property(format: 'email')]
    #[Assert\Email(
        message: 'Parameter is not a valid email',
    )]
    #[CustomAssert\UniqueEntityField(User::class, 'email', ['currentUser'])]
    public readonly string $email;
}