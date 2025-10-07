<?php

declare(strict_types=1);

namespace App\Enum\WeekdayWorkingHours;

use App\Enum\Interface\NormalizerGroupInterface;
use App\Enum\Trait\NormalizerGroupTrait;

enum WeekdayWorkingHoursNormalizerGroup: string implements NormalizerGroupInterface
{
    use NormalizerGroupTrait;

    case DEFAULT = 'weekday_working_hours-default';
}