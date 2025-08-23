<?php

declare(strict_types=1);

namespace App\Response;

class UnauthorizedResponse extends ClientErrorResponse
{
    public const RESPONSE_STATUS = 401;
    public const RESPONSE_MESSAGE = 'Unauthorized';

    public function __construct(string $message = self::RESPONSE_MESSAGE, array $headers = [])
    {
        return parent::__construct(self::RESPONSE_STATUS, $message, null, $headers);
    }
} 