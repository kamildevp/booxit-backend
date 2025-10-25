<?php

declare(strict_types=1);

namespace App\Tests\Feature\User\DataProvider;

use App\DataFixtures\Test\Global\VerifiedUserFixtures;
use App\Enum\TranslationsLocale;
use App\Tests\Utils\DataProvider\BaseDataProvider;

class UserPatchDataProvider extends BaseDataProvider 
{
    public static function validDataCases()
    {
        return [
            [
                [
                    'name' => 'New Test User',
                    'email' => VerifiedUserFixtures::VERIFIED_USER_EMAIL,
                    'verification_handler' => self::VERIFICATION_HANDLER,
                    'language_preference' => TranslationsLocale::EN->value,
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
                    'verification_handler' => self::VERIFICATION_HANDLER,
                    'language_preference' => TranslationsLocale::EN->value,
                ],
                [
                    'email' => VerifiedUserFixtures::VERIFIED_USER_EMAIL,
                ],
                true
            ],
            [
                [
                    'name' => VerifiedUserFixtures::VERIFIED_USER_NAME,
                    'email' => VerifiedUserFixtures::VERIFIED_USER_EMAIL,
                    'verification_handler' => self::VERIFICATION_HANDLER,
                    'language_preference' => TranslationsLocale::EN->value,
                ],
                [
                    'email' => VerifiedUserFixtures::VERIFIED_USER_EMAIL,
                ],
                false
            ],
            [
                [
                    'name' => 'Test User',
                    'email' => VerifiedUserFixtures::VERIFIED_USER_EMAIL,
                    'verification_handler' => self::VERIFICATION_HANDLER,
                    'language_preference' => TranslationsLocale::PL->value,
                ],
                [
                    'language_preference' => TranslationsLocale::PL->value,
                ],
                false
            ],
        ];
    }

    public static function validationDataCases()
    {
        return [
            [
                [
                    'name' => '',
                    'email' => '',
                    'verification_handler' => '',
                    'language_preference' => '',
                ],
                [
                    'name' => [
                        'Parameter must be at least 6 characters long',
                    ],
                    'email' => [
                        'This value should not be blank.',
                    ],
                    'verification_handler' => [
                        'This value should not be blank.'
                    ],
                    'language_preference' => [
                        'Parameter must be one of valid locales: '.implode(', ', array_map(fn($val) => '"'.$val.'"', TranslationsLocale::values())),
                    ],
                ]
            ],
            [
                [
                    'name' => 'Test Name',
                    'email' => 'user',
                    'verification_handler' => 'invalid',
                    'language_preference' => 'a',
                ],
                [
                    'email' => [
                        'Parameter is not a valid email',
                    ],
                    'verification_handler' => [
                        'Invalid verification handler'
                    ],
                    'language_preference' => [
                        'Parameter must be one of valid locales: '.implode(', ', array_map(fn($val) => '"'.$val.'"', TranslationsLocale::values())),
                    ],
                ]
            ],
            [
                [
                    'name' => 'long nameeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeee',
                    'email' => 'verifieduser@example.com',
                    'verification_handler' => self::VERIFICATION_HANDLER,
                    'language_preference' => TranslationsLocale::EN->value,
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
                    'language_preference' => TranslationsLocale::EN->value,
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