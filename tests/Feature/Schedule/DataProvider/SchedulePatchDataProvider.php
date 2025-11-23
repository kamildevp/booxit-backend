<?php

declare(strict_types=1);

namespace App\Tests\Feature\Schedule\DataProvider;

use App\Tests\Utils\DataProvider\BaseDataProvider;

class SchedulePatchDataProvider extends BaseDataProvider 
{
    public static function validDataCases()
    {
        return [
            [
                [
                    'name' => 'New Test Schedule',
                    'description' => 'new test',
                    'division' => 30,
                ],
                [
                    'name' => 'New Test Schedule',
                    'description' => 'new test',
                    'division' => 30,
                ],
            ],
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
                ],
                [
                    'name' => [
                        'Parameter must be at least 6 characters long',
                    ],
                    'division' => [
                        'This value should be between 5 and 60.',
                    ],
                ]
            ],
            [
                [
                    'name' => str_repeat('a', 51),
                    'description' => str_repeat('a', 2001),
                    'division' => 61,
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
                ]
            ],
        ];
    }
}