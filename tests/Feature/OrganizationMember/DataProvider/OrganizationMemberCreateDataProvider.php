<?php

declare(strict_types=1);

namespace App\Tests\Feature\OrganizationMember\DataProvider;

use App\Enum\Organization\OrganizationRole;
use App\Tests\Utils\DataProvider\BaseDataProvider;

class OrganizationMemberCreateDataProvider extends BaseDataProvider 
{
    
    public static function validDataCases()
    {
        return [
            [
                [
                    'role' => OrganizationRole::MEMBER->value,
                ],
                [
                    'role' => OrganizationRole::MEMBER->value,
                ],
            ],
            [
                [
                    'role' => OrganizationRole::ADMIN->value,
                ],
                [
                    'role' => OrganizationRole::ADMIN->value,
                ],
            ],
        ];
    }

    public static function validationDataCases()
    {
        return [
            [
                [
                    'user_id' => 0,
                    'role' => '',
                ],
                [
                    'user_id' => [
                        'User does not exist',
                    ],
                    'role' => [
                        'Parameter must be one of valid roles: '.implode(', ', array_map(fn($val) => '"'.$val.'"', OrganizationRole::values())),
                    ],
                ]
            ],
            [
                [
                    'user_id' => 0,
                    'role' => 'a',
                ],
                [
                    'user_id' => [
                        'User does not exist',
                    ],
                    'role' => [
                        'Parameter must be one of valid roles: '.implode(', ', array_map(fn($val) => '"'.$val.'"', OrganizationRole::values())),
                    ],
                ]
            ],
        ];
    }
}