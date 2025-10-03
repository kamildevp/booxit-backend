<?php

declare(strict_types=1);

namespace App\Tests\Feature\Schedule\DataProvider;

use App\DataFixtures\Test\OrganizationMember\OrganizationAdminFixtures;
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
                    'organization' => [
                        'name' => OrganizationAdminFixtures::ORGANIZATION_NAME
                    ]
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
                    'duration' => '',
                    'estimated_price' => ''
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