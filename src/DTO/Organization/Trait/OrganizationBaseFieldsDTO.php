<?php

declare(strict_types=1);

namespace App\DTO\Organization\Trait;

use App\Entity\Organization;
use App\Validator\Constraints as CustomAssert;
use App\Validator\Constraints\Compound as Compound;

trait OrganizationBaseFieldsDTO
{
    #[CustomAssert\UniqueEntityField(Organization::class, 'name', ['id' => 'organization'])]
    #[Compound\NameRequirements]
    public readonly string $name;

    #[Compound\DescriptionRequirements]
    public readonly string $description; 
}