<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Entity\DataProvider;

use App\Enum\Reservation\ReservationStatus;
use App\Tests\Utils\DataProvider\BaseDataProvider;

class ScheduleReservationServiceDataProvider extends BaseDataProvider
{
    public static function patchScheduleReservationDataCases()
    {
        return [
            [true],
            [false],
        ];
    }

    public static function cancelScheduleReservationConflictDataCases()
    {
        return [
            [ReservationStatus::ORGANIZATION_CANCELLED],
            [ReservationStatus::CUSTOMER_CANCELLED],
        ];
    }
}