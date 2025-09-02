<?php

declare(strict_types=1);

namespace App\Enum;

enum OrderDir: string
{
    case ASC = 'asc';
    case DESC = 'desc';
}