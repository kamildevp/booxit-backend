<?php

namespace App\Tests\Feature\Global\DataProvider;

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
                    'order_dir' => 'invalid_dir'
                ],
                [
                    'order' => [
                        'Specified order columns are invalid'
                    ],
                    'order_dir' => [
                        'Specified order directions does not match order columns',
                        'Specified order directions are invalid',
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
                [
                    'order' => 'created_at',
                    'order_dir' => 'asc',
                ],
                self::getSortedColumnValueSequence('created_at', 'datetime')
            ],
            [
                [
                    'order' => 'created_at',
                    'order_dir' => 'desc',
                ],
                self::getSortedColumnValueSequence('created_at', 'datetime', 'desc')
            ],
            [
                [
                    'order' => 'updated_at',
                    'order_dir' => 'asc',
                ],
                self::getSortedColumnValueSequence('updated_at', 'datetime')
            ],
            [
                [
                    'order' => 'updated_at',
                    'order_dir' => 'desc',
                ],
                self::getSortedColumnValueSequence('updated_at', 'datetime', 'desc')
            ],
        ];
    }

    public static function getSortedColumnValueSequence(string $columnName, string $type, string $dir = 'asc'): ?array
    {
        $data = [
            'string' => [
                [$columnName => 'Sorted A User'],
                [$columnName => 'Sorted B User'],
                [$columnName => 'Sorted C User'],
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