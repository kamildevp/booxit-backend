<?php

namespace App\Tests\Feature\Auth\DataProvider;

use App\Tests\Feature\Global\DataProvider\BaseDataProvider;

class AuthLogoutDataProvider extends BaseDataProvider 
{
    public static function validDataCases()
    {
        return [
            [
                [
                    'logout_other_sessions' => false,
                ],
                1
            ],
            [
                [
                    'logout_other_sessions' => true,
                ],
                0
            ]
        ];
    }
}