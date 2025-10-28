<?php

declare(strict_types=1);

namespace App\DTO\Organization;

use App\DTO\AbstractDTO;
use App\DTO\AddressDTO;
use App\Entity\Organization;
use App\Validator\Constraints as CustomAssert;
use App\Validator\Constraints\Compound as Compound;
use Symfony\Component\Validator\Constraints as Assert;

class OrganizationCreateDTO extends AbstractDTO 
{
    public function __construct(
        #[CustomAssert\UniqueEntityField(Organization::class, 'name')]
        #[Compound\NameRequirements]
        public readonly string $name,
        #[Compound\DescriptionRequirements]
        public readonly string $description,
        #[Assert\Valid]
        public readonly AddressDTO $address,
    )
    {

    }
}