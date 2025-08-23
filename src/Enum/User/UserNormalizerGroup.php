<?php

declare(strict_types=1);

namespace App\Enum\User;

use App\Enum\Interface\NormalizerGroupInterface;
use App\Enum\Trait\NormalizerGroupTrait;

enum UserNormalizerGroup: string implements NormalizerGroupInterface
{
    use NormalizerGroupTrait;

    case PUBLIC = 'user-public';
    case PRIVATE = 'user-private';
}