<?php

declare(strict_types=1);

namespace App\Tests\Feature\Auth\DataProvider;

use App\Tests\Utils\DataProvider\BaseDataProvider;

class AuthGoogleLoginDataProvider extends BaseDataProvider 
{
    public static function validDataCases()
    {
        return [
            [
                [
                    'auth_handler' => 'test',
                    'code' => 'test_code',
                    'code_verifier' => 'test_pkce_code'
                ],
            ]
        ];
    }

    public static function invalidAuthParametersDataCases()
    {
        return [
            [
                [],
            ],
            [
                [
                    'auth_handler' => '',
                    'code' => '',
                    'code_verifier' => ''
                ],
            ],
            [
                [
                    'auth_handler' => 'invalid',
                    'code' => 'test_code',
                    'code_verifier' => 'test_pkce_code'
                ],
            ],
        ];
    }
}