<?php

declare(strict_types=1);

namespace App\Tests\Feature\Reservation\DataProvider;

use App\Tests\Utils\DataProvider\BaseDataProvider;

class ReservationAuthDataProvider extends BaseDataProvider 
{
    
    public static function protectedPaths()
    {
        return [
            ['/api/reservations/me', 'POST'],
            ['/api/reservations/{reservation}/confirm', 'POST'],
        ];
    }

    public static function privilegesOnlyPaths()
    {
        return [
            ['/api/reservations/{reservation}/confirm', 'POST', 'user1@example.com'],
            ['/api/reservations/{reservation}/confirm', 'POST', 'om-user1@example.com'],
            ['/api/reservations/{reservation}/confirm', 'POST', 'sa-user2@example.com'],
        ];
    }
}