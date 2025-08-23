<?php

declare(strict_types=1);

namespace App\DTO;

abstract class AbstractDTO
{
    public function toArray(array $skipKeys = []): array
    {
        return array_filter(get_object_vars($this), fn($value, $key) => !in_array($key, $skipKeys), ARRAY_FILTER_USE_BOTH);
    }
}