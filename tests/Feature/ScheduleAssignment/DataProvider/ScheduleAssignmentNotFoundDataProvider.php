<?php

declare(strict_types=1);

namespace App\Tests\Feature\ScheduleAssignment\DataProvider;

use App\Tests\Utils\DataProvider\BaseDataProvider;

class ScheduleAssignmentNotFoundDataProvider extends BaseDataProvider 
{
    public static function dataCases()
    {
        return [
            [
                '/api/schedules/1000/assignments',
                'GET',
                'Schedule not found'
            ],
            [
                '/api/schedules/1000/assignments',
                'POST',
                'Schedule not found'
            ],
            [
                '/api/schedules/{schedule}/assignments/1000',
                'GET',
                'ScheduleAssignment not found'
            ],
            [
                '/api/schedules/1000/assignments/1000',
                'PATCH',
                'Schedule not found'
            ],
            [
                '/api/schedules/{schedule}/assignments/1000',
                'PATCH',
                'ScheduleAssignment not found'
            ],
            [
                '/api/schedules/1000/assignments/1000',
                'DELETE',
                'Schedule not found'
            ],
            [
                '/api/schedules/{schedule}/assignments/1000',
                'DELETE',
                'ScheduleAssignment not found'
            ],
        ];
    }
}