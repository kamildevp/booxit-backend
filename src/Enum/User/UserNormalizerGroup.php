<?php

declare(strict_types=1);

namespace App\Enum\User;

use App\Entity\User;
use App\Enum\Interface\NormalizerGroupInterface;
use App\Enum\NormalizerGroup;
use App\Enum\Trait\NormalizerGroupTrait;

enum UserNormalizerGroup: string implements NormalizerGroupInterface
{
    use NormalizerGroupTrait;

    case PUBLIC = 'user-public';
    case PRIVATE = 'user-private';
    case BASE_INFO = 'user-base_info';
    case TIMESTAMP = User::class.NormalizerGroup::TIMESTAMP->value;
    case AUTHOR_INFO = User::class.NormalizerGroup::AUTHOR_INFO->value;
    
    protected function appendGroups(): array
    { 
        return match($this){
            self::PUBLIC => [self::BASE_INFO->value, self::TIMESTAMP->value],
            self::PRIVATE => self::PUBLIC->normalizationGroups(),
            default => []
        };
    }
}