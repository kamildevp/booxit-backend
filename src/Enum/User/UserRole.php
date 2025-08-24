<?php

declare(strict_types=1);

namespace App\Enum\User;

enum UserRole: string
{
    case REGULAR = 'REGULAR';
    case PREMIUM = 'PREMIUM';

    public static function values(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }
}