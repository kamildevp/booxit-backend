<?php

declare(strict_types=1);

namespace App\Tests\Feature\Auth\DataProvider;

use App\Tests\Utils\DataProvider\BaseDataProvider;

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