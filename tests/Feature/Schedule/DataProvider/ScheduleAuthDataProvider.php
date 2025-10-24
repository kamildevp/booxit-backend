<?php

declare(strict_types=1);

namespace App\Tests\Feature\Schedule\DataProvider;

use App\Tests\Utils\DataProvider\BaseDataProvider;

class ScheduleAuthDataProvider extends BaseDataProvider 
{
    public static function protectedPaths()
    {
        return [
            ['/api/organizations/{organization}/schedules', 'POST'],
            ['/api/organizations/{organization}/schedules/{schedule}', 'PATCH'],
            ['/api/organizations/{organization}/schedules/{schedule}', 'DELETE'],
        ];
    }

    public static function privilegesOnlyPaths()
    {
        return [
            ['/api/organizations/{organization}/schedules', 'POST', 'user1@example.com'],
            ['/api/organizations/{organization}/schedules', 'POST', 'om-user1@example.com'],
            ['/api/organizations/{organization}/schedules/{schedule}', 'PATCH', 'user1@example.com'],
            ['/api/organizations/{organization}/schedules/{schedule}', 'PATCH', 'om-user1@example.com'],
            ['/api/organizations/{organization}/schedules/{schedule}', 'DELETE', 'user1@example.com'],
            ['/api/organizations/{organization}/schedules/{schedule}', 'DELETE', 'om-user1@example.com'],
        ];
    }
}