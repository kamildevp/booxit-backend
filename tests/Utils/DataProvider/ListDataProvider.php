<?php

declare(strict_types=1);

namespace App\Tests\Utils\DataProvider;

use App\Enum\Organization\OrganizationRole;
use App\Enum\Reservation\ReservationStatus;
use App\Enum\Reservation\ReservationType;
use App\Enum\Schedule\ScheduleAccessType;
use App\Enum\Service\ServiceCategory;

class ListDataProvider extends BaseDataProvider 
{    
    public static function listDataCases()
    {
        return [
            [1, 20, 35],
            [2, 20, 35],
            [1, 10, 35],
            [3, 10, 35],
        ];
    }

    public static function validationDataCases()
    {
        return [
            [
                [
                    'page' => -1,
                    'per_page' => -1,
                ],
                [
                    'page' => [
                        'This value should be greater than 0.',
                    ],
                    'per_page' => [
                        'This value should be greater than 0.',
                    ]
                ]
            ],
            [
                [
                    'order' => 'invalid,name',
                ],
                [
                    'order' => [
                        'Specified order columns are invalid'
                    ],
                ]
            ],
        ];
    }

    public static function getTimestampsFiltersValidationDataCases()
    {
        return [
            [
                [
                    'filters' => [
                        'created_from' => 'a',
                        'created_to' => 'a',
                        'updated_from' => 'a',
                        'updated_to' => 'a',
                    ]
                ],
                [
                    'filters' => [
                        'created_from' => [
                            'Parameter must be datetime string in format Y-m-d\\TH:iP',
                        ],
                        'created_to' => [
                            'Parameter must be datetime string in format Y-m-d\\TH:iP',
                        ],
                        'updated_from' => [
                            'Parameter must be datetime string in format Y-m-d\\TH:iP',
                        ],
                        'updated_to' => [
                            'Parameter must be datetime string in format Y-m-d\\TH:iP',
                        ],
                    ]
                ]
            ],
        ];
    }


    public static function getTimestampsFiltersDataCases(): array
    {
        return [
            [
                [
                    'created_from' => '2025-06-13T13:00+01:00'
                ],
                [
                    'created_at' => '2025-06-13T12:20+00:00'
                ],
            ],
            [
                [
                    'created_to' => '2025-05-13T12:25+00:00'
                ],
                [
                    'created_at' => '2025-05-13T12:20+00:00'
                ],
            ],
            [
                [
                    'updated_from' => '2025-06-13T12:00+00:00'
                ],
                [
                    'updated_at' => '2025-06-13T12:20+00:00'
                ],
            ],
            [
                [
                    'updated_to' => '2025-05-13T12:25+00:00'
                ],
                [
                    'updated_at' => '2025-05-13T12:20+00:00'
                ],
            ],
        ];
    }

    public static function getTimestampsSortingDataCases(): array
    {
        return [
            [
                'created_at',
                self::getSortedColumnValueSequence('created_at', 'datetime')
            ],
            [
                '-created_at',
                self::getSortedColumnValueSequence('created_at', 'datetime', 'desc')
            ],
            [
                'updated_at',
                self::getSortedColumnValueSequence('updated_at', 'datetime')
            ],
            [
                '-updated_at',
                self::getSortedColumnValueSequence('updated_at', 'datetime', 'desc')
            ],
        ];
    }

    public static function getSortedColumnValueSequence(string $columnName, string $type, string $dir = 'asc'): ?array
    {
        $data = [
            'string' => [
                [$columnName => 'Sorted A'],
                [$columnName => 'Sorted B'],
                [$columnName => 'Sorted C'],
            ],
            'email' => [
                [$columnName => 'sort_a_user@example.com'],
                [$columnName => 'sort_b_user@example.com'],
                [$columnName => 'sort_c_user@example.com'],
            ],
            'username' => [
                [$columnName => 'Sorted_A'],
                [$columnName => 'Sorted_B'],
                [$columnName => 'Sorted_C'],
            ],
            'boolean' => [
                [$columnName => false],
                [$columnName => false],
                [$columnName => true],
            ],
            'datetime' => [
                [$columnName => '2025-05-13T12:20+00:00'],
                [$columnName => '2025-05-13T12:30+00:00'],
                [$columnName => '2025-06-13T12:20+00:00'],
            ],
            'dateinterval' => [
                [$columnName => 'P0Y0M0DT12H20M'],
                [$columnName => 'P0Y0M0DT12H40M'],
                [$columnName => 'P0Y0M1DT1H0M'],
            ],
            'decimal' => [
                [$columnName => '10.20'],
                [$columnName => '10.30'],
                [$columnName => '20.00'],
            ],
            'organization_role' => [
                [$columnName => OrganizationRole::ADMIN->value],
                [$columnName => OrganizationRole::MEMBER->value],
                [$columnName => OrganizationRole::MEMBER->value],
            ],
            'schedule_access_type' => [
                [$columnName => ScheduleAccessType::READ->value],
                [$columnName => ScheduleAccessType::WRITE->value],
                [$columnName => ScheduleAccessType::WRITE->value],
            ],
            'reservation_type' => [
                [$columnName => ReservationType::CUSTOM->value],
                [$columnName => ReservationType::REGULAR->value],
                [$columnName => ReservationType::REGULAR->value],
            ],
            'reservation_status' => [
                [$columnName => ReservationStatus::CONFIRMED->value],
                [$columnName => ReservationStatus::CUSTOMER_CANCELLED->value],
                [$columnName => ReservationStatus::PENDING->value],
            ],
            'service_category' => [
                [$columnName => ServiceCategory::AUTOMOTIVE->value],
                [$columnName => ServiceCategory::BUSINESS->value],
                [$columnName => ServiceCategory::FINANCE->value],
            ],
            'postal_code' => [
                [$columnName => '30-125'],
                [$columnName => '30-126'],
                [$columnName => '30-127'],
            ],
            'latitude' => [
                [$columnName => 50.06],
                [$columnName => 50.07],
                [$columnName => 50.08],
            ],
            'longitude' => [
                [$columnName => 19.93],
                [$columnName => 19.98],
                [$columnName => 20.05],
            ],
        ];

        $values = array_key_exists($type, $data) ? $data[$type] : null;
        
        return !empty($values) && $dir == 'desc' ? array_reverse($values) : $values;
    }

    public static function getSortedColumnsValuesSequence(array $columnTypes, string $dir = 'asc'): ?array
    {
        $mergedValues = [];
        foreach($columnTypes as $columnName => $columnType){
            $columnValues = self::getSortedColumnValueSequence($columnName, $columnType, $dir);
            if(empty($columnValues)){
                continue;
            }
            
            $mergedValues = array_map(function($mergedItem, $columnItem){
                return array_merge($mergedItem ?? [], $columnItem);
            }, $mergedValues, $columnValues);
        }
        
        return $mergedValues;
    }
}