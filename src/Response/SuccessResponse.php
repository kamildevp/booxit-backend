<?php

namespace App\Response;

use App\Enum\ResponseStatus;

class SuccessResponse extends ApiResponse
{
    public function __construct(mixed $data = null, int $statusCode = 200, array $headers = [])
    {
        parent::__construct([
            'status' => ResponseStatus::SUCCESS, 
            'data' => $data
        ], $statusCode, $headers);
    }
} 