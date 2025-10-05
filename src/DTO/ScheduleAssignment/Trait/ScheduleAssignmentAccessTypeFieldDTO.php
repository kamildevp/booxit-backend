<?php

declare(strict_types=1);

namespace App\DTO\ScheduleAssignment\Trait;

use App\Enum\Schedule\ScheduleAccessType;
use Symfony\Component\Validator\Constraints as Assert;

trait ScheduleAssignmentAccessTypeFieldDTO
{
    #[Assert\Choice(callback: [ScheduleAccessType::class, 'values'], message: 'Parameter must be one of valid access types: {{ choices }}')]
    public readonly string $accessType; 
}