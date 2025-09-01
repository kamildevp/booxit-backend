<?php

declare(strict_types=1);

namespace App\Tests\Feature\User\DataProvider;

use App\Tests\Feature\Global\DataProvider\ListDataProvider;

class UserListDataProvider extends ListDataProvider 
{
    public static function filtersDataCases()
    {
        return array_merge([
            [
                [
                    'name' => 'A'
                ],
                [
                    'name' => 'Sorted A'
                ],
            ],
        ], parent::getTimestampsFiltersDataCases());
    }

    public static function sortingDataCases()
    {
        return array_merge([
            [
                [
                    'order' => 'name',
                    'order_dir' => 'asc',
                ],
                parent::getSortedColumnValueSequence('name', 'string')
            ],
            [
                [
                    'order' => 'name',
                    'order_dir' => 'desc',
                ],
                parent::getSortedColumnValueSequence('name', 'string', 'desc')
            ],
        ], parent::getTimestampsSortingDataCases());
    }

    public static function validationDataCases()
    {
        return array_merge(parent::validationDataCases(), [
            [
                [
                    'name' => '',
                ],
                [
                    'name' => [
                        'Parameter must be at least 1 characters long'
                    ],
                ]
            ],
            [
                [
                    'name' => str_repeat('a', 55),
                ],
                [
                    'name' => [
                        'Parameter cannot be longer than 50 characters'
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
        ]);
    }
}