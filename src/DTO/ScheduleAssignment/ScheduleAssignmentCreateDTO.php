<?php

declare(strict_types=1);

namespace App\DTO\ScheduleAssignment;

use App\DTO\AbstractDTO;
use App\DTO\ScheduleAssignment\Trait\ScheduleAssignmentAccessTypeFieldDTO;
use App\Entity\OrganizationMember;
use App\Validator\Constraints as CustomAssert;

class ScheduleAssignmentCreateDTO extends AbstractDTO 
{
    use ScheduleAssignmentAccessTypeFieldDTO;

    public function __construct(
        #[CustomAssert\EntityExists(OrganizationMember::class, commonRelations: ['organization' => ['schedules', '{route:schedule}']])]
        public readonly int $organizationMemberId,
        string $accessType
    )
    {
        $this->accessType = $accessType;
    }
}