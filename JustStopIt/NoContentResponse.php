<?php

declare(strict_types=1);

namespace App\Response;

class NoContentResponse extends SuccessResponse
{
    public const RESPONSE_STATUS = 204;

    public function __construct(array $headers = [])
    {
        parent::__construct(null, self::RESPONSE_STATUS, $headers);
    }
} 