<?php

declare(strict_types=1);

namespace App\Tests\Feature\User\DataProvider;

use App\DataFixtures\Test\Global\VerifiedUserFixtures;
use App\Tests\Utils\DataProvider\BaseDataProvider;

class UserPatchDataProvider extends BaseDataProvider 
{
    public static function validDataCases()
    {
        return [
            [
                [
                    'name' => 'New Test User',
                    'email' => 'verifieduser@example.com',
                    'verification_handler' => self::VERIFICATION_HANDLER
                ],
                [
                    'name' => 'New Test User',
                ],
                false
            ],
            [
                [
                    'name' => 'Test User',
                    'email' => 'newuser@example.com',
                    'verification_handler' => self::VERIFICATION_HANDLER
                ],
                [
                    'email' => 'newuser@example.com',
                ],
                true
            ],
            [
                [
                    'name' => VerifiedUserFixtures::VERIFIED_USER_NAME,
                    'email' => VerifiedUserFixtures::VERIFIED_USER_EMAIL,
                    'verification_handler' => self::VERIFICATION_HANDLER,
                ],
                [
                    'email' => VerifiedUserFixtures::VERIFIED_USER_EMAIL,
                ],
                false
            ]
        ];
    }

    public static function validationDataCases()
    {
        return [
            [
                [
                    'name' => '',
                    'email' => 'user',
                    'verification_handler' => 'invalid',
                ],
                [
                    'name' => [
                        'Parameter must be at least 6 characters long',
                    ],
                    'email' => [
                        'Parameter is not a valid email',
                    ],
                    'verification_handler' => [
                        'Invalid verification handler'
                    ]
                ]
            ],
            [
                [
                    'name' => 'long nameeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeee',
                    'email' => 'verifieduser@example.com',
                    'verification_handler' => self::VERIFICATION_HANDLER,
                ],
                [
                    'name' => [
                        'Parameter cannot be longer than 50 characters',
                    ],
                ]
            ],
            [
                [
                    'name' => 'New Test User',
                    'email' => 'user1@example.com',
                    'verification_handler' => self::VERIFICATION_HANDLER,
                ],
                [
                    'email' => [
                        'User with provided email already exists',
                    ],
                ]
            ],
        ];
    }
}