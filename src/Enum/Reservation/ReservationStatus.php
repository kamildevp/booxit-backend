<?php

declare(strict_types=1);

namespace App\Enum\Reservation;

enum ReservationStatus: string
{
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case ORGANIZATION_CANCELLED = 'organization_cancelled';
    case CUSTOMER_CANCELLED = 'customer_cancelled';
}