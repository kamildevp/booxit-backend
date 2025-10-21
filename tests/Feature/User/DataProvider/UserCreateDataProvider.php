<?php

declare(strict_types=1);

namespace App\Tests\Feature\User\DataProvider;

use App\Enum\User\UserRole;
use App\Tests\Utils\DataProvider\BaseDataProvider;

class UserCreateDataProvider extends BaseDataProvider 
{
    
    public static function validDataCases()
    {
        return [
            [
                [
                    'name' => 'Test User',
                    'email' => 'user@example.com',
                    'password' => 'password123',
                    'verification_handler' => self::VERIFICATION_HANDLER,
                ],
                [
                    'name' => 'Test User',
                    'email' => 'user@example.com',
                    'verified' => false,
                    'roles' => [UserRole::REGULAR->value, 'ROLE_USER'],
                ]
            ]
        ];
    }

    public static function validationDataCases()
    {
        return [
            [
                [
                    'name' => '',
                    'email' => '',
                    'password' => '',
                    'verification_handler' => '',
                ],
                [
                    'name' => [
                        'Parameter must be at least 6 characters long',
                    ],
                    'email' => [
                        'This value should not be blank.',
                    ],
                    'password' => [
                        'This value should not be blank.',
                    ],
                    'verification_handler' => [
                        'This value should not be blank.'
                    ]
                ]
            ],
            [
                [
                    'name' => 'Test Name',
                    'email' => 'user',
                    'password' => 'pass',
                    'verification_handler' => 'invalid',
                ],
                [
                    'email' => [
                        'Parameter is not a valid email',
                    ],
                    'password' => [
                        'Password length must be from 8 to 20 characters, can contain special characters(!#$%?&*) and must have at least one letter and digit',
                    ],
                    'verification_handler' => [
                        'Invalid verification handler'
                    ]
                ]
            ],
            [
                [
                    'name' => 'long nameeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeee',
                    'email' => 'user@example.com',
                    'password' => 'password123',
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
                    'password' => 'password123',
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