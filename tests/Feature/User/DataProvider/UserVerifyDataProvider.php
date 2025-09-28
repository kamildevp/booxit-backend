<?php

declare(strict_types=1);

namespace App\Tests\Feature\User\DataProvider;

use App\Enum\EmailConfirmationType;
use App\Tests\Utils\DataProvider\BaseDataProvider;

class UserVerifyDataProvider extends BaseDataProvider 
{
    public static function validDataCases()
    {
        return [
            [EmailConfirmationType::USER_VERIFICATION],
            [EmailConfirmationType::EMAIL_VERIFICATION],
        ];
    }

    public static function failureDataCases()
    {
        $paramsCases = [
            [
                [
                    'id' => 0,
                ]
            ],
            [
                [
                    'expires' => 0,
                ]
            ],
            [
                [
                    'token' => 'invalid',
                ]
            ],
            [
                [
                    '_hash' => 'invalid',
                ]
            ],
        ];

        $allowedTypes = [EmailConfirmationType::USER_VERIFICATION, EmailConfirmationType::EMAIL_VERIFICATION];
        $cases = [];
        foreach($allowedTypes as $type){
            $typeCases = array_map(fn($case) => [...$case, $type], $paramsCases);
            $cases = array_merge($cases, $typeCases);
        }

        return array_merge($cases, [
            [
                [
                    'type' => EmailConfirmationType::EMAIL_VERIFICATION->value,
                ],
                EmailConfirmationType::USER_VERIFICATION
            ],
        ]);
    }

    public static function validationDataCases()
    {
        return [
            [
                [],
                [
                    'id' => [
                        'This value should be of type int.',
                    ],
                    'expires' => [
                        'This value should be of type int.',
                    ],
                    'type' => [
                        'This value should be of type string.'
                    ],
                    'token' => [
                        'This value should be of type string.',
                    ],
                    '_hash' => [
                        'This value should be of type string.',
                    ],
                ]
            ],
            [
                [
                    'id' => 1,
                    'expires' => 1231,
                    'type' => 'invalid',
                    'token' => '',
                    '_hash' => '',
                ],
                [
                    'type' => [
                        'Parameter must be one of valid types: '.implode(', ', array_map(fn($val) => '"'.$val.'"', [
                            EmailConfirmationType::USER_VERIFICATION->value,
                            EmailConfirmationType::EMAIL_VERIFICATION->value
                        ]))
                    ],
                    'token' => [
                        'This value should not be blank.',
                    ],
                    '_hash' => [
                        'This value should not be blank.',
                    ],
                ]
            ],
        ];
    }
}