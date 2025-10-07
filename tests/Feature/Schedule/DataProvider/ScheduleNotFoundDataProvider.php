<?php

declare(strict_types=1);

namespace App\Tests\Feature\Schedule\DataProvider;

use App\Tests\Utils\DataProvider\BaseDataProvider;

class ScheduleNotFoundDataProvider extends BaseDataProvider 
{
    public static function dataCases()
    {
        return [
            [
                '/api/schedules/1000',
                'GET',
                'Schedule not found'
            ],
            [
                '/api/schedules/1000',
                'PATCH',
                'Schedule not found'
            ],
            [
                '/api/schedules/1000',
                'DELETE',
                'Schedule not found'
            ],
            [
                '/api/schedules/1000/services',
                'POST',
                'Schedule not found'
            ],
            [
                '/api/schedules/1000/services',
                'GET',
                'Schedule not found'
            ],
            [
                '/api/schedules/{schedule}/services/1000',
                'DELETE',
                'Service not found'
            ],
        ];
    }
}