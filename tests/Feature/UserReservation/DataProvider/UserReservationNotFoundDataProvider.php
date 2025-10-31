<?php

declare(strict_types=1);

namespace App\Tests\Feature\UserReservation\DataProvider;

use App\Tests\Utils\DataProvider\BaseDataProvider;

class UserReservationNotFoundDataProvider extends BaseDataProvider 
{
    public static function dataCases()
    {
        return [
            [
                '/api/users/me/reservations/1000',
                'GET',
            ],
            [
                '/api/users/me/reservations/1000/cancel',
                'POST',
            ],
        ];
    }
}