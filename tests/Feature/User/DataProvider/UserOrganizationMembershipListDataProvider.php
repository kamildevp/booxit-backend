<?php

declare(strict_types=1);

namespace App\Tests\Feature\User\DataProvider;

use App\Enum\Organization\OrganizationRole;
use App\Tests\Utils\DataProvider\ListDataProvider;

class UserOrganizationMembershipListDataProvider extends ListDataProvider 
{
    public static function filtersDataCases()
    {
        return [
            [
                [
                    'organization' => [
                        'name' => 'A'
                    ]
                ],
                [
                    'organization' => [
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
                'organization.name',
                array_map(fn($val) => ['organization' => $val], parent::getSortedColumnValueSequence('name', 'string'))
            ],
            [
                '-organization.name',
                array_map(fn($val) => ['organization' => $val], parent::getSortedColumnValueSequence('name', 'string', 'desc'))
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
                        'organization' => [
                            'name' => '',
                        ]
                    ]
                ],
                [
                    'filters' => [
                        'organization' => [
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
                        'organization' => [
                            'name' => str_repeat('a', 55),
                        ]
                    ]
                ],
                [
                    'filters' => [
                        'organization' => [
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