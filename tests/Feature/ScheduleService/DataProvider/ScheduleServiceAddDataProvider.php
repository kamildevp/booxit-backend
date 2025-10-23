<?php

declare(strict_types=1);

namespace App\Tests\Feature\ScheduleService\DataProvider;

use App\Tests\Utils\DataProvider\BaseDataProvider;

class ScheduleServiceAddDataProvider extends BaseDataProvider 
{
    public static function validationDataCases()
    {
        return [
            [
                [
                    'service_id' => 0,
                ],
                [
                    'service_id' => [
                        'Service does not exist',
                    ],
                ]
            ],
        ];
    }
}