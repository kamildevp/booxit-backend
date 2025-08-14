<?php

namespace App\Enum\User;

use App\Enum\NormalizerGroup;

enum UserNormalizerGroup: string
{
    case PUBLIC = 'user-public';
    case PRIVATE = 'user-private';

    public function normalizationGroups(): array
    {
        return [...$this->appendGroups(), $this->value];
    }

    protected function appendGroups(): array
    {
        return [
            NormalizerGroup::TIMESTAMP->value,
        ];
    }
}