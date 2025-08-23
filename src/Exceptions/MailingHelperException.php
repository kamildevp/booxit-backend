<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;
use Throwable;

class MailingHelperException extends RuntimeException
{
    public function __construct(string $message = "Mailing provider error", int $code = 0, Throwable|null $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

}