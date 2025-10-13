<?php

declare(strict_types=1);

namespace App\Tests\Feature\WorkingHours\DataProvider;

use App\Tests\Utils\DataProvider\BaseDataProvider;

class WorkingHoursAuthDataProvider extends BaseDataProvider 
{
    public static function protectedPaths()
    {
        return [
            ['/api/schedules/{schedule}/weekly-working-hours', 'PUT'],
            ['/api/schedules/{schedule}/custom-working-hours', 'PUT'],
            ['/api/schedules/{schedule}/custom-working-hours/2025-10-10', 'DELETE'],
        ];
    }

    public static function privilegesOnlyPaths()
    {
        return [
            ['/api/schedules/{schedule}/weekly-working-hours', 'PUT', 'user1@example.com'],
            ['/api/schedules/{schedule}/weekly-working-hours', 'PUT', 'om-user1@example.com'],
            ['/api/schedules/{schedule}/weekly-working-hours', 'PUT', 'sa-user2@example.com'],
            ['/api/schedules/{schedule}/custom-working-hours', 'PUT', 'user1@example.com'],
            ['/api/schedules/{schedule}/custom-working-hours', 'PUT', 'om-user1@example.com'],
            ['/api/schedules/{schedule}/custom-working-hours', 'PUT', 'sa-user2@example.com'],
            ['/api/schedules/{schedule}/custom-working-hours/2025-10-10', 'DELETE', 'user1@example.com'],
            ['/api/schedules/{schedule}/custom-working-hours/2025-10-10', 'DELETE', 'om-user1@example.com'],
            ['/api/schedules/{schedule}/custom-working-hours/2025-10-10', 'DELETE', 'sa-user2@example.com'],
        ];
    }
}