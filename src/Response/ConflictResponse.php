<?php

declare(strict_types=1);

namespace App\Response;

class ConflictResponse extends ClientErrorResponse
{
    public const RESPONSE_STATUS = 409;
    public const RESPONSE_MESSAGE = 'Invalid action';

    public function __construct(string $message = self::RESPONSE_MESSAGE, array $headers = [])
    {
        parent::__construct(
            self::RESPONSE_STATUS,
            $message, 
            null,
            $headers
        );
    }
} 