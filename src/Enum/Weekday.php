<?php

declare(strict_types=1);

namespace App\Enum;

use App\Enum\Trait\ValuesTrait;

enum Weekday: string
{
    use ValuesTrait;
    
    case MONDAY = 'monday';
    case TUESDAY = 'tuesday';
    case WEDNESDAY = 'wednesday';
    case THURSDAY = 'thursday';
    case FRIDAY = 'friday';
    case SATURDAY = 'saturday';
    case SUNDAY = 'sunday';
}