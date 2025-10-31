<?php

declare(strict_types=1);

namespace App\DTO\ScheduleAssignment;

use App\DTO\AbstractDTO;
use App\DTO\ScheduleAssignment\Trait\ScheduleAssignmentAccessTypeFieldDTO;

class ScheduleAssignmentPatchDTO extends AbstractDTO 
{
    use ScheduleAssignmentAccessTypeFieldDTO;

    public function __construct(
        string $accessType
    )
    {
        $this->accessType = $accessType;
    }
}