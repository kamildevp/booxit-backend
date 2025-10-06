<?php

declare(strict_types=1);

namespace App\Tests\Feature\ScheduleAssignment\DataProvider;

use App\Enum\Organization\OrganizationRole;
use App\Tests\Utils\DataProvider\BaseDataProvider;

class ScheduleAssignmentAuthDataProvider extends BaseDataProvider 
{
    public static function protectedPaths()
    {
        return [
            ['/api/schedules/{schedule}/assignments', 'POST'],
            ['/api/schedules/{schedule}/assignments', 'GET'],
            ['/api/schedules/{schedule}/assignments/{scheduleAssignment}', 'GET'],
            ['/api/schedules/{schedule}/assignments/{scheduleAssignment}', 'DELETE'],
            ['/api/schedules/{schedule}/assignments/{scheduleAssignment}', 'PATCH'],
        ];
    }

    public static function scheduleManagementPrivilegesOnlyPaths()
    {
        return [
            ['/api/schedules/{schedule}/assignments', 'POST', null],
            ['/api/schedules/{schedule}/assignments', 'POST', OrganizationRole::MEMBER->value],
            ['/api/schedules/{schedule}/assignments', 'GET', null],
            ['/api/schedules/{schedule}/assignments', 'GET', OrganizationRole::MEMBER->value],
            ['/api/schedules/{schedule}/assignments/{scheduleAssignment}', 'GET', null],
            ['/api/schedules/{schedule}/assignments/{scheduleAssignment}', 'GET', OrganizationRole::MEMBER->value],
            ['/api/schedules/{schedule}/assignments/{scheduleAssignment}', 'PATCH', null],
            ['/api/schedules/{schedule}/assignments/{scheduleAssignment}', 'PATCH', OrganizationRole::MEMBER->value],
            ['/api/schedules/{schedule}/assignments/{scheduleAssignment}', 'DELETE', null],
            ['/api/schedules/{schedule}/assignments/{scheduleAssignment}', 'DELETE', OrganizationRole::MEMBER->value],
        ];
    }
}