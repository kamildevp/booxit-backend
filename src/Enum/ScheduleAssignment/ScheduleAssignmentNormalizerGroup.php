<?php

declare(strict_types=1);

namespace App\Enum\ScheduleAssignment;

use App\Enum\Interface\NormalizerGroupInterface;
use App\Enum\OrganizationMember\OrganizationMemberNormalizerGroup;
use App\Enum\Schedule\ScheduleNormalizerGroup;
use App\Enum\Trait\NormalizerGroupTrait;

enum ScheduleAssignmentNormalizerGroup: string implements NormalizerGroupInterface
{
    use NormalizerGroupTrait;

    case PUBLIC = 'schedule_assignment-public';
    case PRIVATE = 'schedule_assignment-private';
    case BASE_INFO = 'schedule_assignment-base_info';
    case ORGANIZATION_MEMBER = 'schedule_assignment-organization_member';
    case SCHEDULE = 'schedule_assignment-schedule';
    case USER_SCHEDULES = 'schedule_assignment-user_schedules';

    protected function appendGroups(): array
    {
        return match($this){
            self::PUBLIC => [self::BASE_INFO->value, ...self::ORGANIZATION_MEMBER->normalizationGroups()],
            self::PRIVATE => self::PUBLIC->normalizationGroups(),
            self::ORGANIZATION_MEMBER => [self::BASE_INFO->value, ...OrganizationMemberNormalizerGroup::PUBLIC->normalizationGroups()],
            self::USER_SCHEDULES => [
                self::BASE_INFO->value, 
                self::SCHEDULE->value, 
                ScheduleNormalizerGroup::BASE_INFO->value, 
                ...ScheduleNormalizerGroup::ORGANIZATION->normalizationGroups()
            ],
            default => []
        };
    }
}