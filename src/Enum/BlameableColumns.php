<?php

declare(strict_types=1);

namespace App\Enum;

use App\Enum\Trait\ValuesTrait;

enum BlameableColumns: string
{
    use ValuesTrait;

    case CREATED_BY = 'created_by';
    case UPDATED_BY = 'updated_by';
}