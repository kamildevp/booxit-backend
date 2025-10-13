<?php

declare(strict_types=1);

namespace App\Tests\Feature\WorkingHours\DataProvider;

use App\Tests\Utils\DataProvider\BaseDataProvider;

class GetCustomWorkingHoursDataProvider extends BaseDataProvider
{
    public static function customWorkingHours()
    {
        return [
            '2025-10-02' => [
                ['start_time' => '09:00', 'end_time' => '17:00']
            ],
            '2025-10-10' => [
                ['start_time' => '09:00', 'end_time' => '11:00'], 
                ['start_time' => '15:00', 'end_time' => '18:00']
            ],
        ];
    }

    public static function dataCases()
    {
        $customWorkingHours = self::customWorkingHours();

        return [
            [
                [
                    'date_from' => '2025-10-01',
                    'date_to' => '2025-10-31'
                ],
                $customWorkingHours
            ],
            [
                [
                    'date_from' => '2025-10-10',
                    'date_to' => '2025-10-10'
                ],
                ['2025-10-10' => $customWorkingHours['2025-10-10']]
            ]
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

    public static function removeDataCases()
    {
        $customWorkingHours = self::customWorkingHours();
        return array_map(fn($date) => [$date], array_keys($customWorkingHours));
    }
}