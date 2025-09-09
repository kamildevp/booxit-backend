<?php

declare(strict_types=1);

namespace App\Enum;

use App\Enum\Trait\ValuesTrait;

enum TimestampsColumns: string
{
    use ValuesTrait;

    case CREATED_AT = 'created_at';
    case UPDATED_AT = 'updated_at';
}