<?php

declare(strict_types=1);

namespace App\Tests\Feature\ScheduleReservation\DataProvider;

use App\Tests\Utils\DataProvider\BaseDataProvider;

class ScheduleReservationAuthDataProvider extends BaseDataProvider 
{
    
    public static function protectedPaths()
    {
        return [
            ['/api/schedules/{schedule}/reservations/custom', 'POST'],
            ['/api/schedules/{schedule}/reservations/{reservation}/confirm', 'POST'],
            ['/api/schedules/{schedule}/reservations/{reservation}/cancel', 'POST'],
            ['/api/schedules/{schedule}/reservations/{reservation}', 'GET'],
            ['/api/schedules/{schedule}/reservations/{reservation}', 'PATCH'],
            ['/api/schedules/{schedule}/reservations/{reservation}', 'DELETE'],
            ['/api/schedules/{schedule}/reservations', 'GET'],
        ];
    }

    public static function privilegesOnlyPaths()
    {
        return [
            ['/api/schedules/{schedule}/reservations/custom', 'POST', 'user1@example.com'],
            ['/api/schedules/{schedule}/reservations/custom', 'POST', 'om-user1@example.com'],
            ['/api/schedules/{schedule}/reservations/custom', 'POST', 'sa-user2@example.com'],
            ['/api/schedules/{schedule}/reservations/{reservation}/confirm', 'POST', 'user1@example.com'],
            ['/api/schedules/{schedule}/reservations/{reservation}/confirm', 'POST', 'om-user1@example.com'],
            ['/api/schedules/{schedule}/reservations/{reservation}/confirm', 'POST', 'sa-user2@example.com'],
            ['/api/schedules/{schedule}/reservations/{reservation}/cancel', 'POST', 'user1@example.com'],
            ['/api/schedules/{schedule}/reservations/{reservation}/cancel', 'POST', 'om-user1@example.com'],
            ['/api/schedules/{schedule}/reservations/{reservation}/cancel', 'POST', 'sa-user2@example.com'],
            ['/api/schedules/{schedule}/reservations/{reservation}', 'GET', 'user1@example.com'],
            ['/api/schedules/{schedule}/reservations/{reservation}', 'GET', 'om-user1@example.com'],
            ['/api/schedules/{schedule}/reservations/{reservation}', 'PATCH', 'user1@example.com'],
            ['/api/schedules/{schedule}/reservations/{reservation}', 'PATCH', 'om-user1@example.com'],
            ['/api/schedules/{schedule}/reservations/{reservation}', 'PATCH', 'sa-user2@example.com'],
            ['/api/schedules/{schedule}/reservations/{reservation}', 'DELETE', 'user1@example.com'],
            ['/api/schedules/{schedule}/reservations/{reservation}', 'DELETE', 'om-user1@example.com'],
            ['/api/schedules/{schedule}/reservations/{reservation}', 'DELETE', 'sa-user2@example.com'],
            ['/api/schedules/{schedule}/reservations/{reservation}', 'DELETE', 'user1@example.com'],
            ['/api/schedules/{schedule}/reservations/{reservation}', 'DELETE', 'om-user1@example.com'],
            ['/api/schedules/{schedule}/reservations', 'GET', 'user1@example.com'],
            ['/api/schedules/{schedule}/reservations', 'GET', 'om-user1@example.com'],
        ];
    }
}