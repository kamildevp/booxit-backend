<?php

declare(strict_types=1);

namespace App\Tests\Feature\Reservation\DataProvider;

use App\Tests\Utils\DataProvider\BaseDataProvider;

class ReservationNotFoundDataProvider extends BaseDataProvider 
{
    public static function dataCases()
    {
        return [
            ['/api/reservations/1000/confirm', 'POST'],
            ['/api/reservations/1000/cancel', 'POST'],
            ['/api/reservations/1000', 'GET'],
            ['/api/reservations/1000', 'PATCH'],
            ['/api/reservations/1000', 'DELETE'],
        ];
    }
}