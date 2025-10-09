<?php

declare(strict_types=1);

namespace App\Enum\WeekdayTimeWindow;

use App\Enum\Interface\NormalizerGroupInterface;
use App\Enum\Trait\NormalizerGroupTrait;

enum WeekdayTimeWindowNormalizerGroup: string implements NormalizerGroupInterface
{
    use NormalizerGroupTrait;

    case DEFAULT = 'weekday_time_window-default';
}