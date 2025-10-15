<?php

declare(strict_types=1);

namespace App\Tests\Feature\WorkingHours\DataProvider;

use App\Tests\Utils\DataProvider\BaseDataProvider;

class UpdateCustomWorkingHoursDataProvider extends BaseDataProvider 
{
    
    public static function validDataCases()
    {
        return [
            [
                [
                    'date' => '2025-01-01',
                    'time_windows' => [['start_time' => '09:00', 'end_time' => '17:00']],
                ]
            ]
        ];
    }

    public static function validationDataCases()
    {
        return [
            [
                [
                    'date' => '',
                    'time_windows' => [['start_time' => '', 'end_time' => '']],
                ],
                [
                    'date' => [
                        'This value should not be blank.'
                    ],
                    'time_windows' => [
                        '0' => [
                            'start_time' => [
                                'This value should not be blank.'
                            ],
                            'end_time' => [
                                'This value should not be blank.'
                            ]
                        ],
                    ],
                ]
            ],
            [
                [
                    'date' => 'a',
                    'time_windows' => [['start_time' => 'a', 'end_time' => 'a'], ['start_time' => '10:00', 'end_time' => '10:05']],
                ],
                [
                    'date' => [
                        'Parameter must be date in format Y-m-d'
                    ],
                    'time_windows' => [
                        '0' => [
                            'start_time' => [
                                'This value is not a valid time.'
                            ],
                            'end_time' => [
                                'This value is not a valid time.'
                            ]
                        ],
                        '1' => [
                            'errors' => [
                                'Time window cannot be shorter than 10 minutes.',
                            ]
                        ]
                    ],
                ]
            ],
            [
                [
                    'date' => '2025-01-01',
                    'time_windows' => [['start_time' => '09:00', 'end_time' => '10:00'], ['start_time' => '09:30', 'end_time' => '14:00']],
                ],
                [
                    'time_windows' => [
                        'errors' => [
                            'Provided working hours are overlapping.'
                        ]
                    ],
                ]
            ],
            [
                [
                    'date' => '2025-10-01',
                    'time_windows' => [['start_time' => '15:00', 'end_time' => '10:00']],
                ],
                [
                    'errors' => [
                        'Provided working hours are overlapping with custom hours for 2025-10-02.'
                    ]
                ]
            ],
        ];
    }
}