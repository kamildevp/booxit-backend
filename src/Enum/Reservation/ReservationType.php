<?php

declare(strict_types=1);

namespace App\Enum\Reservation;

enum ReservationType: string
{
    case REGULAR = 'regular';
    case CUSTOM = 'custom';
}