<?php

namespace App\Tests\Feature\Global\DataProvider;

class ListDataProvider extends BaseDataProvider 
{
    
    public static function listDataCases()
    {
        return [
            [1, 20, 35],
            [2, 20, 35],
            [1, 10, 35],
            [3, 10, 35],
        ];
    }

    public static function validationDataCases()
    {
        return [
            [
                [
                    'page' => -1,
                    'per_page' => -1,
                ],
                [
                    'page' => [
                        'This value should be greater than 0.',
                    ],
                    'per_page' => [
                        'This value should be greater than 0.',
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
        ];
    }
}