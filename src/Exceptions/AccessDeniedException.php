<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

class AccessDeniedException extends RuntimeException
{
    protected int $httpCode = 403;
}