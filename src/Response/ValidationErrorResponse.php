<?php

declare(strict_types=1);

namespace App\Response;

class ValidationErrorResponse extends ClientErrorResponse
{
    public const RESPONSE_STATUS = 422;
    public const RESPONSE_MESSAGE = 'Validation Error';

    public function __construct(mixed $errors = null, array $headers = [])
    {
        return parent::__construct(self::RESPONSE_STATUS, self::RESPONSE_MESSAGE, $errors, $headers);
    }
} 