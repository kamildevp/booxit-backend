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
            ],
            [
                '/api/schedules/1000',
                'PATCH',
            ],
        ];
    }
}