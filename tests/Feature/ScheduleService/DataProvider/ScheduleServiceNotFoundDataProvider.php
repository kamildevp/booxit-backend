<?php

declare(strict_types=1);

namespace App\Tests\Feature\ScheduleService\DataProvider;

use App\Tests\Utils\DataProvider\BaseDataProvider;

class ScheduleServiceNotFoundDataProvider extends BaseDataProvider 
{
    public static function dataCases()
    {
        return [
            ['/api/schedules/1000/services', 'POST', 'Schedule not found'],
            ['/api/schedules/1000/services', 'GET', 'Schedule not found'],
            ['/api/schedules/100/services/1000', 'DELETE', 'Schedule not found'],
            ['/api/schedules/{schedule}/services/1000','DELETE', 'Service not found'],
            ['/api/schedules/1000/services/1000/availability', 'GET', 'Schedule not found'],
            ['/api/schedules/{schedule}/services/1000/availability', 'GET', 'Service not found'],
        ];
    }
}