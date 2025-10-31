<?php

declare(strict_types=1);

namespace App\Enum\Trait;

trait ValuesTrait
{
    public static function values(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }
}