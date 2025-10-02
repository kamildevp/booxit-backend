<?php

declare(strict_types=1);

namespace App\Tests\Feature\Service\DataProvider;

use App\Tests\Utils\DataProvider\ListDataProvider;

class ServiceListDataProvider extends ListDataProvider 
{
    public static function filtersDataCases()
    {
        return array_merge([
            [
                [
                    'name' => 'A',
                ],
                [
                    'name' => 'Sorted A'
                ],
            ],
            [
                [
                    'duration_from' => 'P1D',
                ],
                [
                    'duration' => 'P0Y0M1DT1H0M0S'
                ],
            ],
            [
                [
                    'duration_to' => 'PT12H30M',
                ],
                [
                    'duration' => 'P0Y0M0DT12H20M0S'
                ],
            ],
            [
                [
                    'estimated_price_from' => '15',
                ],
                [
                    'estimated_price' => '20.00'
                ],
            ],
            [
                [
                    'estimated_price_to' => '10.25',
                ],
                [
                    'estimated_price' => '10.20'
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
            [
                'duration',
                parent::getSortedColumnValueSequence('duration', 'dateinterval')
            ],
            [
                '-duration',
                parent::getSortedColumnValueSequence('duration', 'dateinterval', 'desc')
            ],
            [
                'estimated_price',
                parent::getSortedColumnValueSequence('estimated_price', 'decimal')
            ],
            [
                '-estimated_price',
                parent::getSortedColumnValueSequence('estimated_price', 'decimal', 'desc')
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
                            'duration_from' => '',
                            'duration_to' => '',
                            'estimated_price_from' => '',
                            'estimated_price_to' => '',
                        ]
                    ],
                    [
                        'filters' => [
                            'name' => [
                                'Parameter must be at least 1 characters long'
                            ],
                            'duration_from' => [
                                'This value should not be blank.'
                            ],
                            'duration_to' => [
                                'This value should not be blank.'
                            ],
                            'estimated_price_from' => [
                                'This value should not be blank.'
                            ],
                            'estimated_price_to' => [
                                'This value should not be blank.'
                            ],
                        ]
                    ]
                ],
                [
                    [
                        'filters' => [
                            'name' => str_repeat('a', 55),
                            'duration_from' => 'a',
                            'duration_to' => 'a',
                            'estimated_price_from' => 'a',
                            'estimated_price_to' => 'a',
                        ]
                    ],
                    [
                        'filters' => [
                            'name' => [
                                'Parameter cannot be longer than 50 characters'
                            ],
                            'duration_from' => [
                                'Invalid duration format. Must be a valid ISO-8601 interval.'
                            ],
                            'duration_to' => [
                                'Invalid duration format. Must be a valid ISO-8601 interval.'
                            ],
                            'estimated_price_from' => [
                                'Parameter must be a valid number with up to 2 decimals.'
                            ],
                            'estimated_price_to' => [
                                'Parameter must be a valid number with up to 2 decimals.'
                            ],
                        ]
                    ]
                ],
                [
                    [
                        'filters' => [
                            'estimated_price_from' => str_repeat('1', 11),
                            'estimated_price_to' => str_repeat('1', 11),
                        ]
                    ],
                    [
                        'filters' => [
                            'estimated_price_from' => [
                                'Parameter must be between 0 and 999999.99.'
                            ],
                            'estimated_price_to' => [
                                'Parameter must be between 0 and 999999.99.'
                            ],
                        ]
                    ]
                ],
            ]
        );
    }
}