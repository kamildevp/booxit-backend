<?php

namespace App\Response;

use App\Enum\ResponseStatus;

class ValidationFailedResponse extends ApiResponse
{
    public const RESPONSE_STATUS = 422;

    public function __construct(string $message = 'Validation Failed', array $headers = [])
    {
        parent::__construct([
            'status' => ResponseStatus::FAIL, 
            'data' => [
                'message' => $message,
            ]
        ], self:: RESPONSE_STATUS, $headers);
    }
} 