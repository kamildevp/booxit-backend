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
            ['/api/schedule', 'POST'],
            ['/api/schedule/{schedule}', 'PATCH'],
            ['/api/schedule/{schedule}', 'DELETE'],
        ];
    }

    public static function scheduleManagementPrivilegesOnlyPaths()
    {
        return [
            ['/api/schedule', 'POST', null],
            ['/api/schedule', 'POST', null, ['organization_id' => 0]],
            ['/api/schedule', 'POST', null, ['organization_id' => '{organization}']],
            ['/api/schedule', 'POST', OrganizationRole::MEMBER->value, ['organization_id' => '{organization}']],
            ['/api/schedule/{schedule}', 'PATCH', null],
            ['/api/schedule/{schedule}', 'DELETE', null],
            ['/api/schedule/{schedule}', 'PATCH', OrganizationRole::MEMBER->value],
            ['/api/schedule/{schedule}', 'DELETE', OrganizationRole::MEMBER->value],
        ];
    }
}