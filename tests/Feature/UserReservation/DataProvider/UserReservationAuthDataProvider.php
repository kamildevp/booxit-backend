<?php

declare(strict_types=1);

namespace App\Tests\Feature\UserReservation\DataProvider;

use App\Tests\Utils\DataProvider\BaseDataProvider;

class UserReservationAuthDataProvider extends BaseDataProvider 
{
    
    public static function protectedPaths()
    {
        return [
            ['/api/user/me/reservations', 'POST'],
        ];
    }
}