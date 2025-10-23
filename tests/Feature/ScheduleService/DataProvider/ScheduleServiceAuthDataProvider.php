<?php

declare(strict_types=1);

namespace App\Tests\Feature\ScheduleService\DataProvider;

use App\Tests\Utils\DataProvider\BaseDataProvider;

class ScheduleServiceAuthDataProvider extends BaseDataProvider 
{
    public static function protectedPaths()
    {
        return [
            ['/api/schedules/{schedule}/services', 'POST'],
            ['/api/schedules/{schedule}/services/{service}', 'DELETE'],
        ];
    }

    public static function privilegesOnlyPaths()
    {
        return [
            ['/api/schedules/{schedule}/services', 'POST', 'user1@example.com'],
            ['/api/schedules/{schedule}/services', 'POST', 'om-user1@example.com'],
            ['/api/schedules/{schedule}/services/{service}', 'DELETE', 'user1@example.com'],
            ['/api/schedules/{schedule}/services/{service}', 'DELETE', 'om-user1@example.com'],
        ];
    }
}