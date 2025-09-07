<?php

declare(strict_types=1);

namespace App\Enum;

enum NormalizerGroup: string
{
    case TIMESTAMP = 'timestamp';
    case AUTHOR_INFO = 'author_info';
}