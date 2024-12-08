<?php

namespace App\Exceptions;

use RuntimeException;
use Throwable;

class UnauthorizedException extends RuntimeException
{
    public function __construct(string $message = "Unauthorized", int $code = 0, Throwable|null $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}