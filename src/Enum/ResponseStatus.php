<?php

declare(strict_types=1);

namespace App\Enum;

enum ResponseStatus: string
{
    case SUCCESS = 'success';
    case FAIL = 'fail';
    case ERROR = 'error';
}