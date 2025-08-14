<?php

namespace App\Tests\Feature\User\DataProvider;

use App\Tests\Feature\Global\DataProvider\ListDataProvider;

class UserListDataProvider extends ListDataProvider 
{
    public static function filtersDataCases()
    {
        return [
            [
                [
                    'name' => 'User 31'
                ],
                [
                    'name' => 'Test User 31'
                ]
            ],
        ];
    }

    public static function sortingDataCases()
    {
        return [
            [
                [
                    'order' => 'name',
                    'order_dir' => 'asc',
                ],
                [
                    [
                        'name' => 'Sort A User'
                    ],
                    [
                        'name' => 'Sort B User'
                    ],
                    [
                        'name' => 'Sort C User'
                    ],
                ]
            ],
        ];
    }

    public static function validationDataCases()
    {
        return array_merge(parent::validationDataCases(), [
            [
                [
                    'name' => '',
                ],
                [
                    'name' => [
                        'Parameter must be at least 1 characters long'
                    ],
                ]
            ],
            [
                [
                    'name' => str_repeat('a', 55),
                ],
                [
                    'name' => [
                        'Parameter cannot be longer than 50 characters'
                    ]
                ]
            ],
            [
                [
                    'order' => 'invalid,name',
                    'order_dir' => 'invalid_dir'
                ],
                [
                    'order' => [
                        'Specified order columns are invalid'
                    ],
                    'order_dir' => [
                        'Specified order directions does not match order columns',
                        'Specified order directions are invalid',
                    ],
                ]
            ],
        ]);
    }
}