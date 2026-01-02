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
                    'division' => 15,
                    'timezone' => 'Europe/Warsaw',
                ],
                [
                    'name' => 'Test Schedule',
                    'description' => 'test',
                    'division' => 15,
                    'timezone' => 'Europe/Warsaw',
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
                    'division' => 4,
                    'timezone' => '',
                ],
                [
                    'name' => [
                        'Parameter must be at least 6 characters long',
                    ],
                    'division' => [
                        'This value should be between 5 and 60.',
                    ],
                    'timezone' => [
                        'This value should not be blank.',
                    ],
                ]
            ],
            [
                [
                    'name' => str_repeat('a', 51),
                    'description' => str_repeat('a', 2001),
                    'division' => 61,
                    'timezone' => 'a',
                ],
                [
                    'name' => [
                        'Parameter cannot be longer than 50 characters',
                    ],
                    'description' => [
                        'Parameter cannot be longer than 2000 characters',
                    ],
                    'division' => [
                        'This value should be between 5 and 60.',
                    ],
                    'timezone' => [
                        'This value is not a valid timezone.',
                    ],
                ]
            ],
        ];
    }

    public static function conflictDataCases()
    {
        return [
            [
                [
                    'name' => 'Test Schedule',
                    'description' => 'test',
                    'division' => 15,
                    'timezone' => 'Europe/Warsaw',
                ],
                'The organization has already reached its maximum allowed number of schedules.'
            ],
        ];
    }
}