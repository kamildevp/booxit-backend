<?php

declare(strict_types=1);

namespace App\Tests\Feature\Schedule\DataProvider;

use App\Tests\Utils\DataProvider\BaseDataProvider;

class ScheduleNotFoundDataProvider extends BaseDataProvider 
{
    public static function dataCases()
    {
        return [
            ['/api/organizations/1000/schedules', 'POST', 'Organization not found'],
            ['/api/organizations/1000/schedules/1000', 'GET', 'Schedule not found'],
            ['/api/organizations/{organization}/schedules/1000', 'GET', 'Schedule not found'],
            ['/api/organizations/1000/schedules/1000', 'PATCH', 'Organization not found'],
            ['/api/organizations/{organization}/schedules/1000', 'PATCH', 'Schedule not found'],
            ['/api/organizations/1000/schedules/1000', 'PATCH', 'Organization not found'],
            ['/api/organizations/{organization}/schedules/1000', 'PATCH', 'Schedule not found'],
            ['/api/organizations/1000/schedules', 'Get', 'Organization not found'],
        ];
    }
}