<?php

declare(strict_types=1);

namespace App\Enum\User;

use App\Enum\Trait\ValuesTrait;

enum UserRole: string
{
    use ValuesTrait;

    case REGULAR = 'REGULAR';
    case PREMIUM = 'PREMIUM';
}