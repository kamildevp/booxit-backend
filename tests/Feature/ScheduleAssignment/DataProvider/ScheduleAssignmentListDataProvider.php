<?php

declare(strict_types=1);

namespace App\Tests\Feature\ScheduleAssignment\DataProvider;

use App\Enum\Organization\OrganizationRole;
use App\Enum\Schedule\ScheduleAccessType;
use App\Tests\Utils\DataProvider\ListDataProvider;

class ScheduleAssignmentListDataProvider extends ListDataProvider 
{
    public static function filtersDataCases()
    {
        return [
            [
                [
                    'organization_member' => [
                        'app_user' => [
                            'name' => 'A'
                        ]
                    ]
                ],
                [
                    'organization_member' => [
                        'app_user' => [
                            'name' => 'Sorted A'
                        ],
                    ]
                ],
            ],
            [
                [
                    'organization_member' => [
                        'role' => [OrganizationRole::ADMIN->value]
                    ]
                ],
                [
                    'organization_member' => [
                        'role' => OrganizationRole::ADMIN->value
                    ]
                ],
            ],
            [
                [
                    'access_type' => [ScheduleAccessType::READ->value]
                ],
                [
                    'access_type' => ScheduleAccessType::READ->value
                ],
            ],
        ];
    }

    public static function sortingDataCases()
    {
        return [
            [
                'organization_member.app_user.name',
                array_map(fn($val) => ['organization_member' => ['app_user' => $val]], parent::getSortedColumnValueSequence('name', 'string'))
            ],
            [
                '-organization_member.app_user.name',
                array_map(fn($val) => ['organization_member' => ['app_user' => $val]], parent::getSortedColumnValueSequence('name', 'string', 'desc'))
            ],
            [
                'organization_member.role',
                array_map(fn($val) => ['organization_member' => $val], parent::getSortedColumnValueSequence('role', 'organization_role'))
            ],
            [
                '-organization_member.role',
                array_map(fn($val) => ['organization_member' => $val], parent::getSortedColumnValueSequence('role', 'organization_role', 'desc'))
            ],
            [
                'access_type',
                parent::getSortedColumnValueSequence('access_type', 'schedule_access_type')
            ],
            [
                '-access_type',
                parent::getSortedColumnValueSequence('access_type', 'schedule_access_type', 'desc')
            ],
        ];
    }

    public static function validationDataCases()
    {
        return [
            [
                [
                    'filters' => [
                        'organization_member' => [
                            'app_user' => [
                                'name' => '',
                            ],
                            'role' => ['a']
                        ],
                        'access_type' => ['a'],
                    ]
                ],
                [
                    'filters' => [
                        'organization_member' => [
                            'app_user' => [
                                'name' => [
                                    'Parameter must be at least 1 characters long'
                                ],
                            ],
                            'role' => [
                                'One or more of the given values is invalid, allowed values: '.implode(', ', array_map(fn($val) => '"'.$val.'"', OrganizationRole::values())).'.'
                            ],

                        ],
                        'access_type' => [
                            'One or more of the given values is invalid, allowed values: '.implode(', ', array_map(fn($val) => '"'.$val.'"', ScheduleAccessType::values())).'.'
                        ],
                    ]
                ]
            ],
            [
                [
                    'filters' => [
                        'organization_member' => [
                            'app_user' => [
                                'name' => str_repeat('a', 55),
                            ]
                        ]
                    ]
                ],
                [
                    'filters' => [
                        'organization_member' => [
                            'app_user' => [
                                'name' => [
                                    'Parameter cannot be longer than 50 characters'
                                ]
                            ]
                        ]
                    ]
                ]
            ],
        ];
    }
}