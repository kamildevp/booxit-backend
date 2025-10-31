<?php

declare(strict_types=1);

namespace App\Enum\Reservation;

use App\Enum\Trait\ValuesTrait;

enum ReservationStatus: string
{
    use ValuesTrait;

    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case ORGANIZATION_CANCELLED = 'organization_cancelled';
    case CUSTOMER_CANCELLED = 'customer_cancelled';

    public static function getCancelledStatuses(): array
    {
        return [
            self::ORGANIZATION_CANCELLED->value, 
            self::CUSTOMER_CANCELLED->value
        ];
    }
}