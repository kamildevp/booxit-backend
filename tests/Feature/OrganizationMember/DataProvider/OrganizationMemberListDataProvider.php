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
                    'role' => OrganizationRole::ADMIN->value
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
                        ]
                    ]
                ],
                [
                    'filters' => [
                        'app_user' => [
                            'name' => [
                                'Parameter must be at least 1 characters long'
                            ],
                        ]
                    ]
                ]
            ],
            [
                [
                    'filters' => [
                        'app_user' => [
                            'name' => str_repeat('a', 55),
                        ]
                    ]
                ],
                [
                    'filters' => [
                        'app_user' => [
                            'name' => [
                                'Parameter cannot be longer than 50 characters'
                            ]
                        ]
                    ]
                ]
            ],
            [
                [
                    'filters' => [
                        'role' => 'a',
                    ]
                ],
                [
                    'filters' => [
                        'role' => [
                            'Parameter must be one of valid roles: '.implode(', ', array_map(fn($val) => '"'.$val.'"', OrganizationRole::values())),
                        ],
                    ]
                ]
            ],
        ];
    }
}