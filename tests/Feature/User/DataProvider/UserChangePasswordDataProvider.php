<?php

declare(strict_types=1);

namespace App\Tests\Feature\User\DataProvider;

use App\Tests\Feature\Global\DataProvider\BaseDataProvider;

class UserChangePasswordDataProvider extends BaseDataProvider
{
    public static function validDataCases()
    {
        return [
            [
                [
                    'password' => 'newpassword123',
                    'old_password' => 'password123',
                    'logout_other_sessions' => false
                ],
                2
            ],
            [
                [
                    'password' => 'newpassword123',
                    'old_password' => 'password123',
                    'logout_other_sessions' => true
                ],
                1
            ],
        ];
    }

    public static function validationDataCases()
    {
        return [
            [
                [
                    'password' => 'pass2',
                    'old_password' => 'pass',
                    'logout_other_sessions' => false
                ],
                [
                    'password' => [
                        'Password length must be from 8 to 20 characters, can contain special characters(!#$%?&*) and must have at least one letter and digit',
                    ],
                    'old_password' => [
                        'Invalid current password',
                    ],
                ]
            ],
        ];
    }

}