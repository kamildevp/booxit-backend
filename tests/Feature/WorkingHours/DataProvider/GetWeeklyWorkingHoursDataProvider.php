<?php

declare(strict_types=1);

namespace App\Tests\Feature\WorkingHours\DataProvider;

use App\Tests\Utils\DataProvider\BaseDataProvider;

class GetWeeklyWorkingHoursDataProvider extends BaseDataProvider
{
    public static function weeklyWorkingHours()
    {
        return [
            'monday' => [['start_time' => '09:00', 'end_time' => '17:00']],
            'tuesday' => [['start_time' => '09:00', 'end_time' => '17:00']],
            'wednesday' => [['start_time' => '09:00', 'end_time' => '17:00']],
            'thursday' => [['start_time' => '09:00', 'end_time' => '17:00']],
            'friday' => [['start_time' => '09:00', 'end_time' => '17:00']],
            'saturday' => [['start_time' => '09:00', 'end_time' => '11:00'], ['start_time' => '15:00', 'end_time' => '18:00']],
            'sunday' => []
        ];
    }

    public static function dataCases()
    {
        return [
            [array_merge(self::weeklyWorkingHours(), ['timezone' => 'Europe/Warsaw'])]
        ];
    }
}