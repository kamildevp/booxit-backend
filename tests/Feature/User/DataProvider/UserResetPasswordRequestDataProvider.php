<?php

declare(strict_types=1);

namespace App\Tests\Feature\User\DataProvider;

use App\Tests\Feature\Global\DataProvider\BaseDataProvider;

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