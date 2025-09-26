<?php

declare(strict_types=1);

namespace App\Tests\Feature\User\DataProvider;

use App\Tests\Feature\Global\DataProvider\BaseDataProvider;

class UserVerifyDataProvider extends BaseDataProvider 
{
    public static function failureDataCases()
    {
        return [
            [
                [
                    'id' => 0,
                ]
            ],
            [
                [
                    'expires' => 0,
                ]
            ],
            [
                [
                    'type' => 'invalid',
                ]
            ],
            [
                [
                    'token' => 'invalid',
                ]
            ],
            [
                [
                    '_hash' => 'invalid',
                ]
            ],
        ];
    }

    public static function validationDataCases()
    {
        return [
            [
                [],
                [
                    'id' => [
                        'This value should be of type int.',
                    ],
                    'expires' => [
                        'This value should be of type int.',
                    ],
                    'type' => [
                        'This value should be of type string.',
                    ],
                    'token' => [
                        'This value should be of type string.',
                    ],
                    '_hash' => [
                        'This value should be of type string.',
                    ],
                ]
            ],
            [
                [
                    'id' => 1,
                    'expires' => 1231,
                    'type' => '',
                    'token' => '',
                    '_hash' => '',
                ],
                [
                    'type' => [
                        'This value should not be blank.',
                    ],
                    'token' => [
                        'This value should not be blank.',
                    ],
                    '_hash' => [
                        'This value should not be blank.',
                    ],
                ]
            ],
        ];
    }
}