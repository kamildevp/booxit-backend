<?php

declare(strict_types=1);

namespace App\Tests\Feature\User\DataProvider;

use App\Tests\Utils\DataProvider\BaseDataProvider;

class UserResetPasswordRequestDataProvider extends BaseDataProvider
{
    public static function validDataCases()
    {
        return [
            [
                [
                    'email' => 'user1@example.com',
                    'verification_handler' => self::VERIFICATION_HANDLER,
                ],
            ]
        ];
    }

    public static function validationDataCases()
    {
        return [
            [
                [
                    'email' => '',
                    'verification_handler' => '',
                ],
                [
                    'email' => [
                        'This value should not be blank.',
                    ],
                    'verification_handler' => [
                        'This value should not be blank.'
                    ]
                ]
            ],
            [
                [
                    'email' => 'user',
                    'verification_handler' => 'invalid',
                ],
                [
                    'email' => [
                        'Parameter is not a valid email',
                    ],
                    'verification_handler' => [
                        'Invalid verification handler'
                    ]
                ]
            ],
        ];
    }
}