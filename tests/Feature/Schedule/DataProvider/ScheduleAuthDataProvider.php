<?php

declare(strict_types=1);

namespace App\Tests\Feature\Schedule\DataProvider;

use App\Tests\Utils\DataProvider\BaseDataProvider;

class ScheduleAuthDataProvider extends BaseDataProvider 
{
    public static function protectedPaths()
    {
        return [
            ['/api/schedules', 'POST'],
            ['/api/schedules/{schedule}', 'PATCH'],
            ['/api/schedules/{schedule}', 'DELETE'],
        ];
    }

    public static function privilegesOnlyPaths()
    {
        return [
            ['/api/schedules', 'POST', 'user1@example.com'],
            ['/api/schedules', 'POST', 'user1@example.com', ['organization_id' => 0]],
            ['/api/schedules', 'POST', 'user1@example.com', ['organization_id' => '{organization}']],
            ['/api/schedules', 'POST', 'om-user1@example.com', ['organization_id' => '{organization}']],
            ['/api/schedules/{schedule}', 'PATCH', 'user1@example.com'],
            ['/api/schedules/{schedule}', 'DELETE', 'user1@example.com'],
            ['/api/schedules/{schedule}', 'PATCH', 'om-user1@example.com'],
            ['/api/schedules/{schedule}', 'DELETE', 'om-user1@example.com'],
        ];
    }
}