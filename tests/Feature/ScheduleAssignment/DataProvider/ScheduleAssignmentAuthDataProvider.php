<?php

declare(strict_types=1);

namespace App\Tests\Feature\ScheduleAssignment\DataProvider;

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

    public static function privilegesOnlyPaths()
    {
        return [
            ['/api/schedules/{schedule}/assignments', 'POST', 'user1@example.com'],
            ['/api/schedules/{schedule}/assignments', 'POST', 'om-user1@example.com'],
            ['/api/schedules/{schedule}/assignments', 'GET', 'user1@example.com'],
            ['/api/schedules/{schedule}/assignments', 'GET', 'om-user1@example.com'],
            ['/api/schedules/{schedule}/assignments/{scheduleAssignment}', 'GET', 'user1@example.com'],
            ['/api/schedules/{schedule}/assignments/{scheduleAssignment}', 'GET', 'om-user1@example.com'],
            ['/api/schedules/{schedule}/assignments/{scheduleAssignment}', 'PATCH', 'user1@example.com'],
            ['/api/schedules/{schedule}/assignments/{scheduleAssignment}', 'PATCH', 'om-user1@example.com'],
            ['/api/schedules/{schedule}/assignments/{scheduleAssignment}', 'DELETE', 'user1@example.com'],
            ['/api/schedules/{schedule}/assignments/{scheduleAssignment}', 'DELETE', 'om-user1@example.com'],
        ];
    }
}