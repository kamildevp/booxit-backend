<?php

declare(strict_types=1);

namespace App\Tests\Feature\Schedule\DataProvider;

use App\Tests\Utils\DataProvider\BaseDataProvider;
use DateTimeImmutable;

class ScheduleGetAvailabilityDataProvider extends BaseDataProvider 
{
    public static function validDataCases()
    {
        $startDate = (new DateTimeImmutable('monday next week'));
        $endDate = (new DateTimeImmutable('wednesday next week'));

        return [
            [
                [
                    'date_from' => $startDate->format('Y-m-d'),
                    'date_to' => $endDate->format('Y-m-d'),
                ],
                'Test Service 1',
                [
                    $startDate->format('Y-m-d') => [
                        '23:00',
                    ],
                    $startDate->modify('+1 day')->format('Y-m-d') => [
                        '00:00',
                        '01:30'
                    ],
                    $endDate->format('Y-m-d') => [
                        '00:00',
                        '00:15',
                        '00:30',
                        '01:30',
                        '01:45',
                        '02:00',
                        '02:15',
                        '02:30',
                        '02:45',
                        '03:00',
                        '03:15',
                        '03:30',
                    ]
                ]
            ],
            [
                [
                    'date_from' => $startDate->format('Y-m-d'),
                    'date_to' => $endDate->format('Y-m-d'),
                ],
                'Test Service 2',
                [
                    $startDate->format('Y-m-d') => [],
                    $startDate->modify('+1 day')->format('Y-m-d') => [],
                    $endDate->format('Y-m-d') => [
                        '00:00',
                        '01:30',
                        '01:45',
                        '02:00',
                        '02:15',
                        '02:30',
                        '02:45',
                        '03:00',
                    ]
                ]
            ],
            [
                [
                    'date_from' => $startDate->format('Y-m-d'),
                    'date_to' => $endDate->modify('+1 day')->format('Y-m-d'),
                ],
                'Test Service 1',
                [
                    $startDate->format('Y-m-d') => [
                        '23:00',
                    ],
                    $startDate->modify('+1 day')->format('Y-m-d') => [
                        '00:00',
                        '01:30'
                    ],
                    $endDate->format('Y-m-d') => [
                        '00:00',
                        '00:15',
                        '00:30',
                        '01:30',
                        '01:45',
                        '02:00',
                        '02:15',
                        '02:30',
                        '02:45',
                        '03:00',
                        '03:15',
                        '03:30',
                    ],
                    $endDate->modify('+1 day')->format('Y-m-d') => []
                ]
            ],
        ];
    }

    public static function validationDataCases()
    {
        return [
            [
                [
                    'date_from' => '',
                    'date_to' => '',
                ],
                [
                    'date_from' => [
                        'This value should not be blank.'
                    ],
                    'date_to' => [
                        'This value should not be blank.'
                    ]
                ],
            ],
            [
                [
                    'date_from' => '2025-10-01',
                ],
                [
                    'date_to' => [
                        'The end date must be provided when a start date is specified.'
                    ]
                ],
            ],
            [
                [
                    'date_to' => '2025-10-31',
                ],
                [
                    'date_from' => [
                        'The start date must be provided when an end date is specified.'
                    ]
                ],
            ],
            [
                [
                    'date_from' => 'a',
                    'date_to' => 'a',
                ],
                [
                    'date_to' => [
                        'Parameter must be date in format Y-m-d'
                    ],
                    'date_from' => [
                        'Parameter must be date in format Y-m-d'
                    ]
                ],
            ],
            [
                [
                    'date_from' => '2025-10-10',
                    'date_to' => '2025-10-01',
                ],
                [
                    'date_to' => [
                        'The end date cannot be earlier than the start date.'
                    ]
                ],
            ],
            [
                [
                    'date_from' => '2025-10-01',
                    'date_to' => '2025-11-01',
                ],
                [
                    'date_to' => [
                        'The date range cannot exceed 31 days.'
                    ]
                ],
            ],
        ];
    }
}