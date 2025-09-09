<?php

declare(strict_types=1);

namespace App\Tests\Feature\Global\DataProvider;

use App\Enum\Organization\OrganizationRole;

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

        public static function getTimestampsFiltersDataCases(): array
    {
        return [
            [
                [
                    'created_from' => '2025-06-13T12:00:00+00:00'
                ],
                [
                    'created_at' => '2025-06-13T12:20:00+00:00'
                ],
            ],
            [
                [
                    'created_to' => '2025-05-13T12:25:00+00:00'
                ],
                [
                    'created_at' => '2025-05-13T12:20:00+00:00'
                ],
            ],
            [
                [
                    'updated_from' => '2025-06-13T12:00:00+00:00'
                ],
                [
                    'updated_at' => '2025-06-13T12:20:00+00:00'
                ],
            ],
            [
                [
                    'updated_to' => '2025-05-13T12:25:00+00:00'
                ],
                [
                    'updated_at' => '2025-05-13T12:20:00+00:00'
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
            'boolean' => [
                [$columnName => false],
                [$columnName => false],
                [$columnName => true],
            ],
            'datetime' => [
                [$columnName => '2025-05-13T12:20:00+00:00'],
                [$columnName => '2025-05-13T12:30:00+00:00'],
                [$columnName => '2025-06-13T12:20:00+00:00'],
            ],
            'organization_role' => [
                [$columnName => OrganizationRole::ADMIN->value],
                [$columnName => OrganizationRole::MEMBER->value],
                [$columnName => OrganizationRole::MEMBER->value],
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