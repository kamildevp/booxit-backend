<?php

declare(strict_types=1);

namespace App\Tests\Feature\Reservation\DataProvider;

use App\Tests\Utils\DataProvider\BaseDataProvider;

class ReservationConfirmDataProvider extends BaseDataProvider 
{
    
    public static function validDataCases()
    {
        return [
            [
                [
                    'verification_handler' => self::VERIFICATION_HANDLER,
                ]
            ]
        ];
    }

    public static function validationDataCases()
    {
        return [
            [
                [
                    'verification_handler' => 'a',
                ],
                [
                    'verification_handler' => [
                        'Invalid verification handler'
                    ]
                ]
            ],
        ];
    }
}