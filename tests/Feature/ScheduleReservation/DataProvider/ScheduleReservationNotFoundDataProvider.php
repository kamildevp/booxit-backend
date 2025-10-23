<?php

declare(strict_types=1);

namespace App\Tests\Feature\ScheduleReservation\DataProvider;

use App\Tests\Utils\DataProvider\BaseDataProvider;

class ScheduleReservationNotFoundDataProvider extends BaseDataProvider 
{
    public static function dataCases()
    {
        return [
            ['/api/schedules/1000/reservations', 'POST', 'Schedule not found'],
            ['/api/schedules/1000/reservations/custom', 'POST', 'Schedule not found'],
            ['/api/schedules/1000/reservations/1000/confirm', 'POST', 'Schedule not found'],
            ['/api/schedules/{schedule}/reservations/1000/confirm', 'POST', 'Reservation not found'],
            ['/api/schedules/1000/reservations/1000/cancel', 'POST', 'Schedule not found'],
            ['/api/schedules/{schedule}/reservations/1000/cancel', 'POST', 'Reservation not found'],
            ['/api/schedules/1000/reservations/1000', 'GET', 'Schedule not found'],
            ['/api/schedules/{schedule}/reservations/1000', 'GET', 'Reservation not found'],
            ['/api/schedules/1000/reservations/1000', 'PATCH', 'Schedule not found'],
            ['/api/schedules/{schedule}/reservations/1000', 'PATCH', 'Reservation not found'],
            ['/api/schedules/1000/reservations/1000', 'DELETE', 'Schedule not found'],
            ['/api/schedules/{schedule}/reservations/1000', 'DELETE', 'Reservation not found'],
        ];
    }
}