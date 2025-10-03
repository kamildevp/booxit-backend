<?php

declare(strict_types=1);

namespace App\DTO\Schedule;

use App\DTO\AbstractDTO;
use App\DTO\Attribute\EntityReference;
use App\DTO\Schedule\Trait\ScheduleBaseFieldsDTO;
use App\Entity\Organization;
use App\Validator\Constraints as CustomAssert;

class ScheduleCreateDTO extends AbstractDTO 
{
    use ScheduleBaseFieldsDTO;

    public function __construct(
        #[EntityReference(Organization::class, 'organization')]
        #[CustomAssert\EntityExists(Organization::class)]
        public readonly int $organizationId,
        string $name,
        string $description,
    )
    {
        $this->name = $name;
        $this->description = $description;
    }
}