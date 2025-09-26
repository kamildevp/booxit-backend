<?php

declare(strict_types=1);

namespace App\Tests\Feature\User\DataProvider;

use App\Tests\Feature\Global\DataProvider\BaseDataProvider;

class UserResetPasswordDataProvider extends BaseDataProvider 
{
    public static function validDataCases()
    {
        return [
            [
                [
                    'password' => 'newpassword123',
                ],
            ],
        ];
    }

    public static function failureDataCases()
    {
        return [
            [
                [
                    'id' => 0,
                    'password' => 'newpassword123',
                ]
            ],
            [
                [
                    'expires' => 0,
                    'password' => 'newpassword123',
                ]
            ],
            [
                [
                    'type' => 'invalid',
                    'password' => 'newpassword123',
                ]
            ],
            [
                [
                    'token' => 'invalid',
                    'password' => 'newpassword123',
                ]
            ],
            [
                [
                    '_hash' => 'invalid',
                    'password' => 'newpassword123',
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
                    'password' => [
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
                    'password' => 'pass2',
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
                    'password' => [
                        'Password length must be from 8 to 20 characters, can contain special characters(!#$%?&*) and must have at least one letter and digit'
                    ]
                ]
            ],
        ];
    }
}