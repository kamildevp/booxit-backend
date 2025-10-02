<?php

declare(strict_types=1);

namespace App\DTO\Service;

use App\DTO\AbstractDTO;
use App\DTO\Attribute\EntityReference;
use App\DTO\Service\Trait\ServiceBaseFieldsDTO;
use App\Entity\Organization;
use App\Validator\Constraints as CustomAssert;

class ServiceCreateDTO extends AbstractDTO 
{
    use ServiceBaseFieldsDTO;

    public function __construct(
        #[EntityReference(Organization::class, 'organization')]
        #[CustomAssert\EntityExists(Organization::class)]
        public readonly int $organizationId,
        string $name,
        string $description,
        string $duration,
        string $estimatedPrice
    )
    {
        $this->name = $name;
        $this->description = $description;
        $this->duration = $duration;
        $this->estimatedPrice = $estimatedPrice;
    }
}