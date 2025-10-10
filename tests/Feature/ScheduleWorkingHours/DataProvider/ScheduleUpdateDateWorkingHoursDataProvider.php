<?php

declare(strict_types=1);

namespace App\Tests\Feature\ScheduleWorkingHours\DataProvider;

use App\Tests\Utils\DataProvider\BaseDataProvider;

class ScheduleUpdateDateWorkingHoursDataProvider extends BaseDataProvider 
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
                    'time_windows' => [['start_time' => '09:01', 'end_time' => 'a'], ['start_time' => '10:00', 'end_time' => '09:00']],
                ],
                [
                    'date' => [
                        'Parameter must be date in format Y-m-d'
                    ],
                    'time_windows' => [
                        '0' => [
                            'start_time' => [
                                'Time must be in HH:MM format with minutes being 00, 15, 30, or 45.'
                            ],
                            'end_time' => [
                                'This value is not a valid time.'
                            ]
                        ],
                        '1' => [
                            'errors' => [
                                'Start time must be earlier than end time.',
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
                            'Invalid timewindow collection.'
                        ]
                    ],
                ]
            ],
            [
                [
                    'date' => '2025-01-01',
                    'time_windows' => [['start_time' => '09:00', 'end_time' => '10:00'], ['start_time' => '10:00', 'end_time' => '14:00']],
                ],
                [
                    'time_windows' => [
                        'errors' => [
                            'Invalid timewindow collection.'
                        ]
                    ],
                ]
            ],
        ];
    }
}