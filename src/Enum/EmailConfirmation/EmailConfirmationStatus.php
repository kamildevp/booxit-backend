<?php

declare(strict_types=1);

namespace App\Enum\EmailConfirmation;

use App\Enum\Trait\ValuesTrait;

enum EmailConfirmationStatus: string
{
    use ValuesTrait;

    case PENDING = 'pending';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
}