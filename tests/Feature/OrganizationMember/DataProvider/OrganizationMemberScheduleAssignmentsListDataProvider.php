<?php

declare(strict_types=1);

namespace App\Tests\Feature\OrganizationMember\DataProvider;

use App\Enum\Schedule\ScheduleAccessType;
use App\Tests\Utils\DataProvider\ListDataProvider;

class OrganizationMemberScheduleAssignmentsListDataProvider extends ListDataProvider 
{
    public static function filtersDataCases()
    {
        return [
            [
                [
                    'schedule' => [
                        'name' => 'A'
                    ],
                ],
                [
                    'schedule' => [
                        'name' => 'Sorted A'
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
                'schedule.name',
                array_map(fn($val) => ['schedule' => $val], parent::getSortedColumnValueSequence('name', 'string'))
            ],
            [
                '-schedule.name',
                array_map(fn($val) => ['schedule' => $val], parent::getSortedColumnValueSequence('name', 'string', 'desc'))
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
                        'schedule' => [
                            'name' => '',
                        ],
                        'access_type' => ['a'],
                    ]
                ],
                [
                    'filters' => [
                        'schedule' => [
                            'name' => [
                                'Parameter must be at least 1 characters long'
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
                        'schedule' => [
                            'name' => str_repeat('a', 55),
                        ]
                    ]
                ],
                [
                    'filters' => [
                        'schedule' => [
                            'name' => [
                                'Parameter cannot be longer than 50 characters'
                            ]
                        ]
                    ]
                ]
            ],
        ];
    }
}