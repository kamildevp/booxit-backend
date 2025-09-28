<?php

declare(strict_types=1);

namespace App\Tests\Feature\User\DataProvider;

use App\Enum\EmailConfirmationType;
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
                        'This value should be of type string.'
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
                    'type' => 'invalid',
                    'token' => '',
                    '_hash' => '',
                ],
                [
                    'type' => [
                        'Parameter must be one of valid types: '.implode(', ', array_map(fn($val) => '"'.$val.'"', [
                            EmailConfirmationType::USER_VERIFICATION->value,
                            EmailConfirmationType::EMAIL_VERIFICATION->value
                        ]))
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