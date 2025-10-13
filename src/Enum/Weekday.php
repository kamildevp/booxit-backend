<?php

declare(strict_types=1);

namespace App\Enum;

use App\Enum\Trait\ValuesTrait;
use ValueError;

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

    public static function createFromInt(int $weekdayNumber)
    {
        
        $case = match($weekdayNumber){
            1 => self::MONDAY,
            2 => self::TUESDAY,
            3 => self::WEDNESDAY,
            4 => self::THURSDAY,
            5 => self::FRIDAY,
            6 => self::SATURDAY,
            7 => self::SUNDAY,
            default => null
        };

        if(is_null($case)){
            throw new ValueError("No matching case for value \"$weekdayNumber\"");
        }
        
        return $case;
    }
}