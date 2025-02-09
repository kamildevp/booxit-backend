<?php

namespace App\Response;

class UnauthorizedResponse extends ClientErrorResponse
{
    public const RESPONSE_STATUS = 401;

    public function __construct(string $message = 'Unauthorized', array $headers = [])
    {
        return parent::__construct(self::RESPONSE_STATUS, $message, null, $headers);
    }
} 