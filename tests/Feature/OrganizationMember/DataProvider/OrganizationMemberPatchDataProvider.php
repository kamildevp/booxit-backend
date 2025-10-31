<?php

declare(strict_types=1);

namespace App\Tests\Feature\OrganizationMember\DataProvider;

use App\Enum\Organization\OrganizationRole;
use App\Tests\Utils\DataProvider\BaseDataProvider;

class OrganizationMemberPatchDataProvider extends BaseDataProvider 
{
    public static function validDataCases()
    {
        return [
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
                    'role' => '',
                ],
                [
                    'role' => [
                        'Parameter must be one of valid roles: '.implode(', ', array_map(fn($val) => '"'.$val.'"', OrganizationRole::values())),
                    ],
                ]
            ],
            [
                [
                    'role' => 'a',
                ],
                [
                    'role' => [
                        'Parameter must be one of valid roles: '.implode(', ', array_map(fn($val) => '"'.$val.'"', OrganizationRole::values())),
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
                    'role' => OrganizationRole::MEMBER->value,
                ],
                'The admin role cannot be removed because this user is the only administrator of the organization.'
            ],
        ];
    }
}