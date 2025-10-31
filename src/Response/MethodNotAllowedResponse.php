<?php

declare(strict_types=1);

namespace App\Response;

use App\Enum\ResponseStatus;

class MethodNotAllowedResponse extends ApiResponse
{
    public const RESPONSE_STATUS = 405;
    public const RESPONSE_MESSAGE = 'Method not allowed';

    public function __construct(string $message = self::RESPONSE_MESSAGE, array $headers = [])
    {
        parent::__construct([
            'status' => ResponseStatus::FAIL, 
            'data' => [
                'message' => $message,
            ]
        ], self:: RESPONSE_STATUS, $headers);
    }
} 