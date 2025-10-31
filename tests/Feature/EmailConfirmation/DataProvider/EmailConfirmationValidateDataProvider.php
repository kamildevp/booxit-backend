<?php

declare(strict_types=1);

namespace App\Tests\Feature\EmailConfirmation\DataProvider;

use App\Enum\EmailConfirmation\EmailConfirmationType;
use App\Tests\Utils\DataProvider\BaseDataProvider;

class EmailConfirmationValidateDataProvider extends BaseDataProvider 
{
    public static function validDataCases()
    {
        return [
            [EmailConfirmationType::ACCOUNT_ACTIVATION],
            [EmailConfirmationType::EMAIL_VERIFICATION],
            [EmailConfirmationType::PASSWORD_RESET],
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

        $cases = [];
        foreach(EmailConfirmationType::cases() as $type){
            $typeCases = array_map(fn($case) => [...$case, $type], $paramsCases);
            $cases = array_merge($cases, $typeCases);
        }

        return array_merge($cases, [
            [
                [
                    'type' => EmailConfirmationType::ACCOUNT_ACTIVATION->value,
                ],
                EmailConfirmationType::EMAIL_VERIFICATION
            ],
        ]);
    }

    public static function validationDataCases()
    {
        return [
                        [
                [
                    'id' => 'a',
                    'expires' => 'a',
                    'type' => 'type',
                    'token' => 'token',
                    '_hash' => 'signature',
                ],
                [
                    'id' => [
                        'This value should be of type int.'
                    ],
                    'expires' => [
                        'This value should be of type int.'
                    ],
                ]
            ],
            [
                [
                    'id' => 1,
                    'expires' => 12323,
                    'type' => 'invalid',
                    'token' => '',
                    '_hash' => '',
                ],
                [
                    'type' => [
                        'Parameter must be one of valid types: '.implode(', ', array_map(fn($val) => '"'.$val.'"', EmailConfirmationType::values()))
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