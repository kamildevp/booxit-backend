<?php

declare(strict_types=1);

namespace App\Tests\Feature\Reservation\DataProvider;

use App\Tests\Utils\DataProvider\BaseDataProvider;

class ReservationAuthDataProvider extends BaseDataProvider 
{
    
    public static function protectedPaths()
    {
        return [
            ['/api/reservations/custom', 'POST'],
            ['/api/reservations/{reservation}/confirm', 'POST'],
            ['/api/reservations/{reservation}/organization-cancel', 'POST'],
            ['/api/reservations/{reservation}', 'GET'],
            ['/api/reservations/{reservation}', 'PATCH'],
            ['/api/reservations/{reservation}', 'DELETE'],
        ];
    }

    public static function privilegesOnlyPaths()
    {
        return [
            ['/api/reservations/custom', 'POST', 'user1@example.com', ['schedule_id' => 0]],
            ['/api/reservations/custom', 'POST', 'user1@example.com', ['schedule_id' => '{schedule}']],
            ['/api/reservations/custom', 'POST', 'om-user1@example.com', ['schedule_id' => 0]],
            ['/api/reservations/custom', 'POST', 'sa-user2@example.com', ['schedule_id' => '{schedule}']],
            ['/api/reservations/{reservation}/confirm', 'POST', 'user1@example.com'],
            ['/api/reservations/{reservation}/confirm', 'POST', 'om-user1@example.com'],
            ['/api/reservations/{reservation}/confirm', 'POST', 'sa-user2@example.com'],
            ['/api/reservations/{reservation}/organization-cancel', 'POST', 'user1@example.com'],
            ['/api/reservations/{reservation}/organization-cancel', 'POST', 'om-user1@example.com'],
            ['/api/reservations/{reservation}/organization-cancel', 'POST', 'sa-user2@example.com'],
            ['/api/reservations/{reservation}', 'GET', 'user1@example.com'],
            ['/api/reservations/{reservation}', 'GET', 'om-user1@example.com'],
            ['/api/reservations/{reservation}', 'PATCH', 'user1@example.com', ['schedule_id' => 0]],
            ['/api/reservations/{reservation}', 'PATCH', 'user1@example.com', ['schedule_id' => '{schedule}']],
            ['/api/reservations/{reservation}', 'PATCH', 'om-user1@example.com', ['schedule_id' => 0]],
            ['/api/reservations/{reservation}', 'PATCH', 'om-user1@example.com', ['schedule_id' => '{schedule}']],
            ['/api/reservations/{reservation}', 'PATCH', 'sa-user2@example.com', ['schedule_id' => 0]],
            ['/api/reservations/{reservation}', 'PATCH', 'sa-user2@example.com', ['schedule_id' => '{schedule}']],
            ['/api/reservations/{reservation}', 'DELETE', 'user1@example.com'],
            ['/api/reservations/{reservation}', 'DELETE', 'om-user1@example.com'],
            ['/api/reservations/{reservation}', 'DELETE', 'sa-user2@example.com'],
        ];
    }
}