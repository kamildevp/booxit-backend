<?php

declare(strict_types=1);

namespace App\Tests\Feature\Service\DataProvider;

use App\Enum\Service\ServiceCategory;
use App\Tests\Utils\DataProvider\BaseDataProvider;

class ServiceCreateDataProvider extends BaseDataProvider 
{
    
    public static function validDataCases()
    {
        return [
            [
                [
                    'name' => 'Test Service',
                    'category' => ServiceCategory::OTHER->value,
                    'description' => 'test',
                    'duration' => 'PT01H30M',
                    'estimated_price' => '15.5',
                ],
                [
                    'name' => 'Test Service',
                    'category' => ServiceCategory::OTHER->value,
                    'description' => 'test',
                    'duration' => 'P0Y0M0DT1H30M',
                    'estimated_price' => '15.5',
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
                    'category' => '',
                    'description' => '',
                    'duration' => '',
                    'estimated_price' => ''
                ],
                [
                    'name' => [
                        'Parameter must be at least 6 characters long',
                    ],
                    'category' => [
                        'Parameter must be one of valid categories: '.implode(', ', array_map(fn($val) => '"'.$val.'"', ServiceCategory::values())),
                    ],
                    'duration' => [
                        'This value should not be blank.'
                    ],
                    'estimated_price' => [
                        'This value should not be blank.'
                    ]
                ]
            ],
            [
                [
                    'name' => str_repeat('a', 51),
                    'category' => 'a',
                    'description' => str_repeat('a', 2001),
                    'duration' => 'a',
                    'estimated_price' => 'a'
                ],
                [
                    'name' => [
                        'Parameter cannot be longer than 50 characters',
                    ],
                    'category' => [
                        'Parameter must be one of valid categories: '.implode(', ', array_map(fn($val) => '"'.$val.'"', ServiceCategory::values())),
                    ],
                    'description' => [
                        'Parameter cannot be longer than 2000 characters',
                    ],
                    'duration' => [
                        'Invalid duration format. Must be a valid ISO-8601 interval without seconds.'
                    ],
                    'estimated_price' => [
                        'Parameter must be a valid number with up to 2 decimals.',
                    ]
                ]
            ],
            [
                [
                    'name' => 'Test Service',
                    'category' => ServiceCategory::OTHER->value,
                    'description' => 'test',
                    'duration' => 'PT1M',
                    'estimated_price' => str_repeat('1', 11),
                ],
                [
                    'estimated_price' => [
                        'Parameter must be between 0 and 999999.99.'
                    ],
                    'duration' => [
                        'Duration cannot be shorter than 10 minutes.'
                    ]
                ]
            ],
            [
                [
                    'name' => 'Test Service',
                    'category' => ServiceCategory::OTHER->value,
                    'description' => 'test',
                    'duration' => 'P1DT1M',
                    'estimated_price' => '25.25',
                ],
                [
                    'duration' => [
                        'Duration cannot be longer than 1 day.'
                    ]
                ]
            ],
        ];
    }
}