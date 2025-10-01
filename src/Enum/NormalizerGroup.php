<?php

declare(strict_types=1);

namespace App\Enum;

use App\Enum\Interface\NormalizerGroupInterface;
use App\Enum\User\UserNormalizerGroup;

enum NormalizerGroup: string implements NormalizerGroupInterface
{
    case TIMESTAMP = 'timestamp';
    case AUTHOR_INFO = 'author_info';
    
    public function normalizationGroups(): array
    {
        return match($this){
            self::AUTHOR_INFO => UserNormalizerGroup::BASE_INFO->normalizationGroups(),
            default => []
        };
    }

    
}