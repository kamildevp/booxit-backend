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
        ];
    }
}