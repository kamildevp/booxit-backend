<?php

declare(strict_types=1);

namespace App\Tests\Feature\Schedule\DataProvider;

use App\Tests\Utils\DataProvider\BaseDataProvider;

class ScheduleCreateDataProvider extends BaseDataProvider 
{
    
    public static function validDataCases()
    {
        return [
            [
                [
                    'name' => 'Test Schedule',
                    'description' => 'test',
                ],
                [
                    'name' => 'Test Schedule',
                    'description' => 'test',
                ]
            ]
        ];
    }

    public static function validationDataCases()
    {
        return [
            [
                [
                    'name' => '',
                    'description' => '',
                ],
                [
                    'name' => [
                        'Parameter must be at least 6 characters long',
                    ],
                ]
            ],
            [
                [
                    'name' => str_repeat('a', 51),
                    'description' => str_repeat('a', 2001),
                ],
                [
                    'name' => [
                        'Parameter cannot be longer than 50 characters',
                    ],
                    'description' => [
                        'Parameter cannot be longer than 2000 characters',
                    ],
                ]
            ],
        ];
    }
}