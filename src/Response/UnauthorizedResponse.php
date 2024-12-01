<?php

namespace App\Response;

use App\Enum\ResponseStatus;

class UnauthorizedResponse extends ClientErrorResponse
{
    public const RESPONSE_STATUS = 401;
    public const RESPONSE_MESSAGE = 'Unauthorized';

    public function __construct(array $headers = [])
    {
        return parent::__construct(self::RESPONSE_STATUS, self::RESPONSE_MESSAGE, null, $headers);
    }
} 