<?php

namespace App\Exceptions;

use RuntimeException;

class AccessDeniedException extends RuntimeException
{
    protected int $httpCode = 403;
}