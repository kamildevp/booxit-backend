<?php

declare(strict_types=1);

namespace App\Enum\Schedule;

use App\Enum\Trait\ValuesTrait;

enum ScheduleAccessType: string
{
    use ValuesTrait;

    case READ = 'READ';
    case WRITE = 'WRITE';
}