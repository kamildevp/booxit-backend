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
                    'wednesday' => [['start_time' => '09:00', 'end_time' => '17:00']],
                    'thursday' => [['start_time' => '09:00', 'end_time' => '17:00']],
                    'friday' => [['start_time' => '09:00', 'end_time' => '17:00']],
                    'saturday' => [['start_time' => '09:00', 'end_time' => '11:00'], ['start_time' => '15:00', 'end_time' => '18:00']],
                    'sunday' => []
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
                    ]
                ]
            ],
            [
                [
                    'monday' => [['start_time' => '09:01', 'end_time' => 'a'], ['start_time' => '10:00', 'end_time' => '09:00']],
                    'tuesday' => [['start_time' => '09:01', 'end_time' => 'a'], ['start_time' => '10:00', 'end_time' => '09:00']],
                    'wednesday' => [['start_time' => '09:01', 'end_time' => 'a'], ['start_time' => '10:00', 'end_time' => '09:00']],
                    'thursday' => [['start_time' => '09:01', 'end_time' => 'a'], ['start_time' => '10:00', 'end_time' => '09:00']],
                    'friday' => [['start_time' => '09:01', 'end_time' => 'a'], ['start_time' => '10:00', 'end_time' => '09:00']],
                    'saturday' => [['start_time' => '09:01', 'end_time' => 'a'], ['start_time' => '10:00', 'end_time' => '09:00']],
                    'sunday' => [['start_time' => '09:01', 'end_time' => 'a'], ['start_time' => '10:00', 'end_time' => '09:00']],
                ],
                [
                    'monday' => [
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
                    'tuesday' => [
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
                    'wednesday' => [
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
                    'thursday' => [
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
                    'friday' => [
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
                    'saturday' => [
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
                    'sunday' => [
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
                ],
                [
                    'monday' => [
                        'errors' => [
                            'Invalid timewindow collection.'
                        ]
                    ],
                    'tuesday' => [
                        'errors' => [
                            'Invalid timewindow collection.'
                        ]
                    ],
                    'wednesday' => [
                        'errors' => [
                            'Invalid timewindow collection.'
                        ]
                    ],
                    'thursday' => [
                        'errors' => [
                            'Invalid timewindow collection.'
                        ]
                    ],
                    'friday' => [
                        'errors' => [
                            'Invalid timewindow collection.'
                        ]
                    ],
                    'saturday' => [
                        'errors' => [
                            'Invalid timewindow collection.'
                        ]
                    ],
                    'sunday' => [
                        'errors' => [
                            'Invalid timewindow collection.'
                        ]
                    ]
                ]
            ],
            [
                [
                    'monday' => [['start_time' => '09:00', 'end_time' => '10:00'], ['start_time' => '10:00', 'end_time' => '14:00']],
                    'tuesday' => [['start_time' => '09:00', 'end_time' => '10:00'], ['start_time' => '10:00', 'end_time' => '14:00']],
                    'wednesday' => [['start_time' => '09:00', 'end_time' => '10:00'], ['start_time' => '10:00', 'end_time' => '14:00']],
                    'thursday' => [['start_time' => '09:00', 'end_time' => '10:00'], ['start_time' => '10:00', 'end_time' => '14:00']],
                    'friday' => [['start_time' => '09:00', 'end_time' => '10:00'], ['start_time' => '10:00', 'end_time' => '14:00']],
                    'saturday' => [['start_time' => '09:00', 'end_time' => '10:00'], ['start_time' => '10:00', 'end_time' => '14:00']],
                    'sunday' => [['start_time' => '09:00', 'end_time' => '10:00'], ['start_time' => '10:00', 'end_time' => '14:00']],
                ],
                [
                    'monday' => [
                        'errors' => [
                            'Invalid timewindow collection.'
                        ]
                    ],
                    'tuesday' => [
                        'errors' => [
                            'Invalid timewindow collection.'
                        ]
                    ],
                    'wednesday' => [
                        'errors' => [
                            'Invalid timewindow collection.'
                        ]
                    ],
                    'thursday' => [
                        'errors' => [
                            'Invalid timewindow collection.'
                        ]
                    ],
                    'friday' => [
                        'errors' => [
                            'Invalid timewindow collection.'
                        ]
                    ],
                    'saturday' => [
                        'errors' => [
                            'Invalid timewindow collection.'
                        ]
                    ],
                    'sunday' => [
                        'errors' => [
                            'Invalid timewindow collection.'
                        ]
                    ]
                ]
            ],
        ];
    }
}