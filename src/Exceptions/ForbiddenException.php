<?php

namespace App\Exceptions;

use RuntimeException;
use Throwable;

class ForbiddenException extends RuntimeException
{
    public function __construct(string $message = "Forbidden", int $code = 0, Throwable|null $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}