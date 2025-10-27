<?php

declare(strict_types=1);

namespace App\Tests\Feature\Organization\DataProvider;

use App\Enum\Service\ServiceCategory;
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
            [
                [
                    'service_category' => [ServiceCategory::BUSINESS->value]
                ],
                [
                    'name' => 'Sorted B'
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
                            'service_category' => ['a'],
                        ]
                    ],
                    [
                        'filters' => [
                            'name' => [
                                'Parameter must be at least 1 characters long'
                            ],
                            'service_category' => [
                                'One or more of the given values is invalid, allowed values: '.implode(', ', array_map(fn($val) => '"'.$val.'"', ServiceCategory::values())).'.'
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