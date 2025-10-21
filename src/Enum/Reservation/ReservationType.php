<?php

declare(strict_types=1);

namespace App\Enum\Reservation;

use App\Enum\Trait\ValuesTrait;

enum ReservationType: string
{
    use ValuesTrait;

    case REGULAR = 'regular';
    case CUSTOM = 'custom';
}