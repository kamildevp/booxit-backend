<?php

declare(strict_types=1);

namespace App\Enum\Trait;

trait NormalizerGroupTrait
{
    public function normalizationGroups(): array
    {
        return [...$this->appendGroups(), $this->value];
    }

    protected function appendGroups(): array
    {
        return [];
    }
}