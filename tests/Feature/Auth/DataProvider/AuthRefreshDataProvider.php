<?php

declare(strict_types=1);

namespace App\Tests\Feature\Auth\DataProvider;

use App\DataFixtures\Test\Global\VerifiedUserFixtures;
use App\Tests\Feature\Global\DataProvider\BaseDataProvider;

class AuthRefreshDataProvider extends BaseDataProvider 
{
    public static function validDataCases()
    {
        return [
            [
                [
                    'email' => VerifiedUserFixtures::VERIFIED_USER_EMAIL,
                ],
            ]
        ];
    }

    public static function invalidCredentialsDataCases()
    {
        return [
            [
                [
                    'email' => VerifiedUserFixtures::VERIFIED_USER_EMAIL,
                    'password' => 'invalid',
                ],
            ],
            [
                [
                    'email' => 'invalid',
                    'password' => VerifiedUserFixtures::VERIFIED_USER_PASSWORD,
                ],
            ],
        ];
    }
}