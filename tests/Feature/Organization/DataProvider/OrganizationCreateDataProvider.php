<?php

declare(strict_types=1);

namespace App\Tests\Feature\Organization\DataProvider;

use App\Tests\Feature\Global\DataProvider\BaseDataProvider;

class OrganizationCreateDataProvider extends BaseDataProvider 
{
    
    public static function validDataCases()
    {
        return [
            [
                [
                    'name' => 'Test Organization',
                    'description' => 'test',
                ],
                [
                    'name' => 'Test Organization',
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
            [
                [
                    'name' => 'Test Organization 1',
                    'description' => '',
                ],
                [
                    'name' => [
                        'Organization with provided name already exists',
                    ],
                ]
            ],
        ];
    }
}