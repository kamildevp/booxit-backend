<?php

declare(strict_types=1);

namespace App\Tests\Feature\Organization\DataProvider;

use App\Tests\Utils\DataProvider\ListDataProvider;

class OrganizationListDataProvider extends ListDataProvider 
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
                'name',
                parent::getSortedColumnValueSequence('name', 'string')
            ],
            [
                '-name',
                parent::getSortedColumnValueSequence('name', 'string', 'desc')
            ],
        ], parent::getTimestampsSortingDataCases());
    }

    public static function validationDataCases()
    {
        return array_merge(
            parent::validationDataCases(), 
            parent::getTimestampsFiltersValidationDataCases(), 
            [
                [
                    [
                        'filters' => [
                            'name' => '',
                        ]
                    ],
                    [
                        'filters' => [
                            'name' => [
                                'Parameter must be at least 1 characters long'
                            ],
                        ]
                    ]
                ],
                [
                    [
                        'filters' => [
                            'name' => str_repeat('a', 55),
                        ]
                    ],
                    [
                        'filters' => [
                            'name' => [
                                'Parameter cannot be longer than 50 characters'
                            ]
                        ]
                    ]
                ],
            ]
        );
    }
}