<?php

declare(strict_types=1);

namespace App\Tests\Feature\OrganizationMember\DataProvider;

use App\Enum\Organization\OrganizationRole;
use App\Tests\Utils\DataProvider\ListDataProvider;

class OrganizationMemberListDataProvider extends ListDataProvider 
{
    public static function filtersDataCases()
    {
        return [
            [
                [
                    'app_user' => [
                        'name' => 'A'
                    ]
                ],
                [
                    'app_user' => [
                        'name' => 'Sorted A'
                    ]
                ],
            ],
            [
                [
                    'app_user' => [
                        'username' => 'A'
                    ]
                ],
                [
                    'app_user' => [
                        'username' => 'Sorted_A'
                    ]
                ],
            ],
            [
                [
                    'role' => [OrganizationRole::ADMIN->value]
                ],
                [
                    'role' => OrganizationRole::ADMIN->value
                ],
            ],
        ];
    }

    public static function sortingDataCases()
    {
        return [
            [
                'app_user.name',
                array_map(fn($val) => ['app_user' => $val], parent::getSortedColumnValueSequence('name', 'string'))
            ],
            [
                '-app_user.name',
                array_map(fn($val) => ['app_user' => $val], parent::getSortedColumnValueSequence('name', 'string', 'desc'))
            ],
            [
                'app_user.username',
                array_map(fn($val) => ['app_user' => $val], parent::getSortedColumnValueSequence('username', 'username'))
            ],
            [
                '-app_user.username',
                array_map(fn($val) => ['app_user' => $val], parent::getSortedColumnValueSequence('username', 'username', 'desc'))
            ],
            [
                'role',
                parent::getSortedColumnValueSequence('role', 'organization_role')
            ],
            [
                '-role',
                parent::getSortedColumnValueSequence('role', 'organization_role', 'desc')
            ],
        ];
    }

    public static function validationDataCases()
    {
        return [
            [
                [
                    'filters' => [
                        'app_user' => [
                            'name' => '',
                            'username' => '',
                        ],
                        'role' => ['a'],
                    ]
                ],
                [
                    'filters' => [
                        'app_user' => [
                            'name' => [
                                'Parameter must be at least 1 characters long'
                            ],
                            'username' => [
                                'Parameter must be at least 1 characters long'
                            ],
                        ],
                        'role' => [
                            'One or more of the given values is invalid, allowed values: '.implode(', ', array_map(fn($val) => '"'.$val.'"', OrganizationRole::values())).'.'
                        ],
                    ]
                ]
            ],
            [
                [
                    'filters' => [
                        'app_user' => [
                            'name' => str_repeat('a', 55),
                            'username' => str_repeat('a', 55),
                        ]
                    ]
                ],
                [
                    'filters' => [
                        'app_user' => [
                            'name' => [
                                'Parameter cannot be longer than 50 characters'
                            ],
                            'username' => [
                                'Parameter cannot be longer than 50 characters'
                            ]
                        ]
                    ]
                ]
            ],
        ];
    }
}