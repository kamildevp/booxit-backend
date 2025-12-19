<?php

declare(strict_types=1);

namespace App\Tests\Feature\WorkingHours\DataProvider;

use App\Tests\Utils\DataProvider\BaseDataProvider;

class UpdateWeeklyWorkingHoursDataProvider extends BaseDataProvider 
{
    
    public static function validDataCases()
    {
        return [
            [
                [
                    'monday' => [['start_time' => '09:00', 'end_time' => '17:00']],
                    'tuesday' => [['start_time' => '09:00', 'end_time' => '17:00']],
                    'wednesday' => [['start_time' => '09:00', 'end_time' => '02:00']],
                    'thursday' => [['start_time' => '09:00', 'end_time' => '17:00']],
                    'friday' => [['start_time' => '09:00', 'end_time' => '17:00']],
                    'saturday' => [['start_time' => '09:00', 'end_time' => '11:00'], ['start_time' => '15:00', 'end_time' => '18:00']],
                    'sunday' => [],
                    'timezone' => 'UTC'
                ]
            ]
        ];
    }

    public static function validationDataCases()
    {
        return [
            [
                [
                    'monday' => [['start_time' => '', 'end_time' => '']],
                    'tuesday' => [['start_time' => '', 'end_time' => '']],
                    'wednesday' => [['start_time' => '', 'end_time' => '']],
                    'thursday' => [['start_time' => '', 'end_time' => '']],
                    'friday' => [['start_time' => '', 'end_time' => '']],
                    'saturday' => [['start_time' => '', 'end_time' => '']],
                    'sunday' => [['start_time' => '', 'end_time' => '']],
                    'timezone' => ''
                ],
                [
                    'monday' => [
                        '0' => [
                            'start_time' => [
                                'This value should not be blank.'
                            ],
                            'end_time' => [
                                'This value should not be blank.'
                            ]
                        ],
                    ],
                    'tuesday' => [
                        '0' => [
                            'start_time' => [
                                'This value should not be blank.'
                            ],
                            'end_time' => [
                                'This value should not be blank.'
                            ]
                        ],
                    ],
                    'wednesday' => [
                        '0' => [
                            'start_time' => [
                                'This value should not be blank.'
                            ],
                            'end_time' => [
                                'This value should not be blank.'
                            ]
                        ],
                    ],
                    'thursday' => [
                        '0' => [
                            'start_time' => [
                                'This value should not be blank.'
                            ],
                            'end_time' => [
                                'This value should not be blank.'
                            ]
                        ],
                    ],
                    'friday' => [
                        '0' => [
                            'start_time' => [
                                'This value should not be blank.'
                            ],
                            'end_time' => [
                                'This value should not be blank.'
                            ]
                        ],
                    ],
                    'saturday' => [
                        '0' => [
                            'start_time' => [
                                'This value should not be blank.'
                            ],
                            'end_time' => [
                                'This value should not be blank.'
                            ]
                        ],
                    ],
                    'sunday' => [
                        '0' => [
                            'start_time' => [
                                'This value should not be blank.'
                            ],
                            'end_time' => [
                                'This value should not be blank.'
                            ]
                        ],
                    ],
                    'timezone' => [
                        'This value should not be blank.'
                    ]
                ]
            ],
            [
                [
                    'monday' => [['start_time' => 'a', 'end_time' => 'a'], ['start_time' => '10:00', 'end_time' => '10:05']],
                    'tuesday' => [['start_time' => 'a', 'end_time' => 'a'], ['start_time' => '10:00', 'end_time' => '10:05']],
                    'wednesday' => [['start_time' => 'a', 'end_time' => 'a'], ['start_time' => '10:00', 'end_time' => '10:05']],
                    'thursday' => [['start_time' => 'a', 'end_time' => 'a'], ['start_time' => '10:00', 'end_time' => '10:05']],
                    'friday' => [['start_time' => 'a', 'end_time' => 'a'], ['start_time' => '10:00', 'end_time' => '10:05']],
                    'saturday' => [['start_time' => 'a', 'end_time' => 'a'], ['start_time' => '10:00', 'end_time' => '10:05']],
                    'sunday' => [['start_time' => 'a', 'end_time' => 'a'], ['start_time' => '10:00', 'end_time' => '10:05']],
                    'timezone' => 'a',
                ],
                [
                    'monday' => [
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
                    'tuesday' => [
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
                    'wednesday' => [
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
                    'thursday' => [
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
                    'friday' => [
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
                    'saturday' => [
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
                    'sunday' => [
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
                    'timezone' => [
                        'This value is not a valid timezone.'
                    ]
                ]
            ],
            [
                [
                    'monday' => [['start_time' => '09:00', 'end_time' => '10:00'], ['start_time' => '09:30', 'end_time' => '14:00']],
                    'tuesday' => [['start_time' => '09:00', 'end_time' => '10:00'], ['start_time' => '09:30', 'end_time' => '14:00']],
                    'wednesday' => [['start_time' => '09:00', 'end_time' => '10:00'], ['start_time' => '09:30', 'end_time' => '14:00']],
                    'thursday' => [['start_time' => '09:00', 'end_time' => '10:00'], ['start_time' => '09:30', 'end_time' => '14:00']],
                    'friday' => [['start_time' => '09:00', 'end_time' => '10:00'], ['start_time' => '09:30', 'end_time' => '14:00']],
                    'saturday' => [['start_time' => '09:00', 'end_time' => '10:00'], ['start_time' => '09:30', 'end_time' => '14:00']],
                    'sunday' => [['start_time' => '09:00', 'end_time' => '10:00'], ['start_time' => '09:30', 'end_time' => '14:00']],
                    'timezone' => 'Europe/Warsaw',
                ],
                [
                    'errors' => [
                        'Provided working hours are overlapping.'
                    ]
                ]
            ],
            [
                [
                    'monday' => [['start_time' => '09:00', 'end_time' => '04:00']],
                    'tuesday' => [['start_time' => '03:00', 'end_time' => '10:00']],
                    'wednesday' => [['start_time' => '09:00', 'end_time' => '10:00']],
                    'thursday' => [['start_time' => '09:00', 'end_time' => '10:00']],
                    'friday' => [['start_time' => '09:00', 'end_time' => '10:00']],
                    'saturday' => [['start_time' => '09:00', 'end_time' => '10:00']],
                    'sunday' => [['start_time' => '09:00', 'end_time' => '10:00']],
                    'timezone' => 'Europe/Warsaw',
                ],
                [
                    'errors' => [
                        'Provided working hours are overlapping.'
                    ]
                ]
            ],
        ];
    }
}