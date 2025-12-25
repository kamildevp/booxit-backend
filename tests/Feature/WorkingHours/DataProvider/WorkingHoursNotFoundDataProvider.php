<?php

declare(strict_types=1);

namespace App\Tests\Feature\WorkingHours\DataProvider;

use App\Tests\Utils\DataProvider\BaseDataProvider;

class WorkingHoursNotFoundDataProvider extends BaseDataProvider 
{
    public static function dataCases()
    {
        return [
            [
                '/api/schedules/1000/weekly-working-hours',
                'GET',
                'Schedule not found'
            ],
            [
                '/api/schedules/1000/weekly-working-hours',
                'PUT',
                'Schedule not found'
            ],
            [
                '/api/schedules/1000/custom-working-hours',
                'GET',
                'Schedule not found'
            ],
            [
                '/api/schedules/1000/custom-working-hours',
                'PUT',
                'Schedule not found'
            ],
        ];
    }
}