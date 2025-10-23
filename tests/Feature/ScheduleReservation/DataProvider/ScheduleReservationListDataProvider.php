<?php

declare(strict_types=1);

namespace App\Tests\Feature\ScheduleReservation\DataProvider;

use App\Enum\Reservation\ReservationStatus;
use App\Enum\Reservation\ReservationType;
use App\Tests\Utils\DataProvider\ListDataProvider;
use App\Validator\Constraints\Compound\DateTimeStringRequirements;

class ScheduleReservationListDataProvider extends ListDataProvider 
{
    public static function filtersDataCases()
    {
        return array_merge([
            [
                [
                    'service_id' => 'Sorted A',
                ],
                [
                    'service' => [
                        'name' => 'Sorted A'
                    ]
                ],
            ],
            [
                [
                    'reserved_by_id' => 'Sorted A',
                ],
                [
                    'reserved_by' => [
                        'name' => 'Sorted A'
                    ]
                ],
            ],
            [
                [
                    'start_date_time_from' => '2025-06-13T13:00+01:00'
                ],
                [
                    'start_date_time' => '2025-06-13T12:20+00:00'
                ],
            ],
            [
                [
                    'start_date_time_to' => '2025-05-13T12:25+00:00'
                ],
                [
                    'start_date_time' => '2025-05-13T12:20+00:00'
                ],
            ],
            [
                [
                    'end_date_time_from' => '2025-06-13T13:00+01:00'
                ],
                [
                    'end_date_time' => '2025-06-13T12:20+00:00'
                ],
            ],
            [
                [
                    'end_date_time_to' => '2025-05-13T12:25+00:00'
                ],
                [
                    'end_date_time' => '2025-05-13T12:20+00:00'
                ],
            ],
            [
                [
                    'expiry_date_from' => '2025-06-13T13:00+01:00'
                ],
                [
                    'expiry_date' => '2025-06-13T12:20+00:00'
                ],
            ],
            [
                [
                    'expiry_date_to' => '2025-05-13T12:25+00:00'
                ],
                [
                    'expiry_date' => '2025-05-13T12:20+00:00'
                ],
            ],
            [
                [
                    'type' => [ReservationType::CUSTOM->value]
                ],
                [
                    'type' => ReservationType::CUSTOM->value
                ],
            ],
            [
                [
                    'status' => [ReservationStatus::CONFIRMED->value]
                ],
                [
                    'status' => ReservationStatus::CONFIRMED->value
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
            [
                [
                    'reference' => 'A',
                ],
                [
                    'reference' => 'Sorted A'
                ],
            ],
            [
                [
                    'email' => 'A',
                ],
                [
                    'email' => 'Sorted A'
                ],
            ],
            [
                [
                    'phone_number' => 'A',
                ],
                [
                    'phone_number' => 'Sorted A'
                ],
            ],
            [
                [
                    'verified' => true,
                ],
                [
                    'verified' => true,
                ],
            ],
        ], parent::getTimestampsFiltersDataCases());
    }

    public static function sortingDataCases()
    {
        return array_merge([
            [
                'reference',
                parent::getSortedColumnValueSequence('reference', 'string')
            ],
            [
                '-reference',
                parent::getSortedColumnValueSequence('reference', 'string', 'desc')
            ],
            [
                'email',
                parent::getSortedColumnValueSequence('email', 'string')
            ],
            [
                '-email',
                parent::getSortedColumnValueSequence('email', 'string', 'desc')
            ],
            [
                'verified',
                parent::getSortedColumnValueSequence('verified', 'boolean')
            ],
            [
                '-verified',
                parent::getSortedColumnValueSequence('verified', 'boolean', 'desc')
            ],
            [
                'expiry_date',
                parent::getSortedColumnValueSequence('expiry_date', 'datetime')
            ],
            [
                '-expiry_date',
                parent::getSortedColumnValueSequence('expiry_date', 'datetime', 'desc')
            ],
            [
                'estimated_price',
                parent::getSortedColumnValueSequence('estimated_price', 'decimal')
            ],
            [
                '-estimated_price',
                parent::getSortedColumnValueSequence('estimated_price', 'decimal', 'desc')
            ],
            [
                'start_date_time',
                parent::getSortedColumnValueSequence('start_date_time', 'datetime')
            ],
            [
                '-start_date_time',
                parent::getSortedColumnValueSequence('start_date_time', 'datetime', 'desc')
            ],
            [
                'end_date_time',
                parent::getSortedColumnValueSequence('end_date_time', 'datetime')
            ],
            [
                '-end_date_time',
                parent::getSortedColumnValueSequence('end_date_time', 'datetime', 'desc')
            ],
            [
                'type',
                parent::getSortedColumnValueSequence('type', 'reservation_type')
            ],
            [
                '-type',
                parent::getSortedColumnValueSequence('type', 'reservation_type', 'desc')
            ],
            [
                'status',
                parent::getSortedColumnValueSequence('status', 'reservation_status')
            ],
            [
                '-status',
                parent::getSortedColumnValueSequence('status', 'reservation_status', 'desc')
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
                            'service_id' => ['a'],
                            'reserved_by_id' => ['a'],
                            'start_date_time_from' => '',
                            'start_date_time_to' => '',
                            'end_date_time_from' => '',
                            'end_date_time_to' => '',
                            'expiry_date_from' => '',
                            'expiry_date_to' => '',
                            'type' => ['a'],
                            'status' => ['a'],
                            'estimated_price_from' => '',
                            'estimated_price_to' => '',
                            'reference' => '',
                            'email' => '',
                            'phone_number' => '',       
                        ]
                    ],
                    [
                        'filters' => [
                            'service_id' => [
                                ['This value should be of type digit.']
                            ],
                            'reserved_by_id' => [
                                ['This value should be of type digit.']
                            ],
                            'start_date_time_from' => [
                                'This value should not be blank.'
                            ],
                            'start_date_time_to' => [
                                'This value should not be blank.'
                            ],
                            'end_date_time_from' => [
                                'This value should not be blank.'
                            ],
                            'end_date_time_to' => [
                                'This value should not be blank.'
                            ],
                            'expiry_date_from' => [
                                'This value should not be blank.'
                            ],
                            'expiry_date_to' => [
                                'This value should not be blank.'
                            ],
                            'type' => [
                                'One or more of the given values is invalid, allowed values: '.implode(', ', array_map(fn($val) => '"'.$val.'"', ReservationType::values())).'.'
                            ],
                            'status' => [
                                'One or more of the given values is invalid, allowed values: '.implode(', ', array_map(fn($val) => '"'.$val.'"', ReservationStatus::values())).'.'
                            ],
                            'estimated_price_from' => [
                                'This value should not be blank.'
                            ],
                            'estimated_price_to' => [
                                'This value should not be blank.'
                            ],
                            'reference' => [
                                'Parameter must be at least 1 characters long'
                            ],
                            'email' => [
                                'Parameter must be at least 1 characters long'
                            ],
                            'phone_number' => [
                                'Parameter must be at least 1 characters long'
                            ],
                        ]
                    ]
                ],
                [
                    [
                        'filters' => [
                            'start_date_time_from' => 'a',
                            'start_date_time_to' => 'a',
                            'end_date_time_from' => 'a',
                            'end_date_time_to' => 'a',
                            'expiry_date_from' => 'a',
                            'expiry_date_to' => 'a',
                            'estimated_price_from' => 'a',
                            'estimated_price_to' => 'a',
                        ]
                    ],
                    [
                        'filters' => [
                            'start_date_time_from' => [
                                'Parameter must be datetime string in format '. DateTimeStringRequirements::FORMAT
                            ],
                            'start_date_time_to' => [
                                'Parameter must be datetime string in format '. DateTimeStringRequirements::FORMAT
                            ],
                            'end_date_time_from' => [
                                'Parameter must be datetime string in format '. DateTimeStringRequirements::FORMAT
                            ],
                            'end_date_time_to' => [
                                'Parameter must be datetime string in format '. DateTimeStringRequirements::FORMAT
                            ],
                            'expiry_date_from' => [
                                'Parameter must be datetime string in format '. DateTimeStringRequirements::FORMAT
                            ],
                            'expiry_date_to' => [
                                'Parameter must be datetime string in format '. DateTimeStringRequirements::FORMAT
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