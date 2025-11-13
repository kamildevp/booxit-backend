<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo\WorkingHours\DataProvider;

use App\Tests\Utils\DataProvider\BaseDataProvider;

class WeeklyWorkingHoursDataProvider extends BaseDataProvider
{
    public static function getData()
    {
        return [
            'monday' => [['start_time' => '09:00', 'end_time' => '17:00']],
            'tuesday' => [['start_time' => '09:00', 'end_time' => '17:00']],
            'wednesday' => [['start_time' => '09:00', 'end_time' => '17:00']],
            'thursday' => [['start_time' => '09:00', 'end_time' => '17:00']],
            'friday' => [['start_time' => '09:00', 'end_time' => '17:00']],
            'saturday' => [],
            'sunday' => []
        ];
    }
}