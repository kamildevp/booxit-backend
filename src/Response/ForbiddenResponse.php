<?php

namespace App\Response;

use App\Enum\ResponseStatus;

class ForbiddenResponse extends ClientErrorResponse
{
    public const RESPONSE_STATUS = 403;
    public const RESPONSE_MESSAGE = 'Forbidden';

    public function __construct(array $headers = [])
    {
        return parent::__construct(self::RESPONSE_STATUS, self::RESPONSE_MESSAGE, null, $headers);
    }
} 