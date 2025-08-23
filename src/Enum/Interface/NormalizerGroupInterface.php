<?php

declare(strict_types=1);

namespace App\Enum\Interface;

interface NormalizerGroupInterface
{
    public function normalizationGroups(): array;
}