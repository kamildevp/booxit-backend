<?php

declare(strict_types=1);

namespace App\Tests\Feature\Schedule\DataProvider;

use App\Enum\Organization\OrganizationRole;
use App\Tests\Utils\DataProvider\BaseDataProvider;

class ScheduleAuthDataProvider extends BaseDataProvider 
{
    public static function protectedPaths()
    {
        return [
            ['/api/schedules', 'POST'],
            ['/api/schedules/{schedule}', 'PATCH'],
            ['/api/schedules/{schedule}', 'DELETE'],
            ['/api/schedules/{schedule}/services', 'POST'],
            ['/api/schedules/{schedule}/services/{service}', 'DELETE'],
        ];
    }

    public static function scheduleManagementPrivilegesOnlyPaths()
    {
        return [
            ['/api/schedules', 'POST', null],
            ['/api/schedules', 'POST', null, ['organization_id' => 0]],
            ['/api/schedules', 'POST', null, ['organization_id' => '{organization}']],
            ['/api/schedules', 'POST', OrganizationRole::MEMBER->value, ['organization_id' => '{organization}']],
            ['/api/schedules/{schedule}', 'PATCH', null],
            ['/api/schedules/{schedule}', 'DELETE', null],
            ['/api/schedules/{schedule}', 'PATCH', OrganizationRole::MEMBER->value],
            ['/api/schedules/{schedule}', 'DELETE', OrganizationRole::MEMBER->value],
            ['/api/schedules/{schedule}/services', 'POST', null],
            ['/api/schedules/{schedule}/services', 'POST', OrganizationRole::MEMBER->value],
            ['/api/schedules/{schedule}/services/{service}', 'DELETE', null],
            ['/api/schedules/{schedule}/services/{service}', 'DELETE', OrganizationRole::MEMBER->value],
        ];
    }
}