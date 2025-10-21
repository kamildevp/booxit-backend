<?php

declare(strict_types=1);

namespace App\Tests\Feature\Reservation\DataProvider;

use App\Tests\Utils\DataProvider\BaseDataProvider;

class ReservationOrganizationCancelDataProvider extends BaseDataProvider 
{
    
    public static function dataCases()
    {
        return [
            [
                [
                    'notify_customer' => true,
                ],
                true
            ],
            [
                [
                    'notify_customer' => false,
                ],
                false
            ],
        ];
    }
}