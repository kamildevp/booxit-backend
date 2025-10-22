<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Entity\DataProvider;

use App\Enum\Reservation\ReservationStatus;
use App\Tests\Utils\DataProvider\BaseDataProvider;

class ReservationServiceDataProvider extends BaseDataProvider
{
    public static function cancelOrganizationReservationDataCases()
    {
        return [
            [true],
            [false],
        ];
    }

    public static function patchReservationDataCases()
    {
        return [
            [true],
            [false],
        ];
    }

    public static function cancelReservationByUrlConflictDataCases()
    {
        return [
            [false, ReservationStatus::PENDING],
            [true, ReservationStatus::ORGANIZATION_CANCELLED],
            [true, ReservationStatus::CUSTOMER_CANCELLED],
        ];
    }

    public static function verifyReservationConflictDataCases()
    {
        return [
            [false, ReservationStatus::PENDING],
            [true, ReservationStatus::CONFIRMED],
            [true, ReservationStatus::ORGANIZATION_CANCELLED],
            [true, ReservationStatus::CUSTOMER_CANCELLED],
        ];
    }
}