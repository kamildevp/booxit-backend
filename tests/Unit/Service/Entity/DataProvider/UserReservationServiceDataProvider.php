<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Entity\DataProvider;

use App\Enum\Reservation\ReservationStatus;
use App\Exceptions\ConflictException;
use App\Exceptions\EntityNotFoundException;
use App\Tests\Utils\DataProvider\BaseDataProvider;

class UserReservationServiceDataProvider extends BaseDataProvider
{
    public static function cancelUserReservationExceptionDataCases()
    {
        return [
            [ReservationStatus::PENDING, false, EntityNotFoundException::class],
            [ReservationStatus::CONFIRMED, false, EntityNotFoundException::class],
            [ReservationStatus::ORGANIZATION_CANCELLED, false, EntityNotFoundException::class],
            [ReservationStatus::ORGANIZATION_CANCELLED, true, ConflictException::class],
            [ReservationStatus::CUSTOMER_CANCELLED, false, EntityNotFoundException::class],
            [ReservationStatus::CUSTOMER_CANCELLED, true, ConflictException::class],
        ];
    }
}