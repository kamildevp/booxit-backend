<?php

declare(strict_types=1);

namespace App\DTO\Organization\Trait;

use App\Entity\Organization;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\Constraints as CustomAssert;

trait OrganizationBaseFieldsDTO
{
    #[CustomAssert\UniqueEntityField(Organization::class, 'name', ['id' => 'organization'])]
    #[Assert\Length(
        min: 6,
        max: 50,
        minMessage: 'Parameter must be at least {{ limit }} characters long',
        maxMessage: 'Parameter cannot be longer than {{ limit }} characters',
    )]
    public readonly string $name;

    #[Assert\Length(
        max: 2000,
        maxMessage: 'Parameter cannot be longer than {{ limit }} characters',
    )]
    public readonly string $description; 
}