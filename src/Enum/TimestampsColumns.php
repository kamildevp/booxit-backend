<?php

namespace App\Enum;

enum TimestampsColumns: string
{
    case CREATED_AT = 'created_at';
    case UPDATED_AT = 'updated_at';

    public static function values(): array
    {
       return array_column(self::cases(), 'value');
    }
}