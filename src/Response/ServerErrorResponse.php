<?php

namespace App\Response;

use App\Enum\ResponseStatus;
use Symfony\Component\HttpFoundation\JsonResponse;

class ServerErrorResponse extends JsonResponse
{
    public function __construct(
        string $message = 'Server Error', 
        int $statusCode = 500, 
        mixed $data = null, 
        ?int $errorCode = null, 
        array $headers = []
        )
    {
        parent::__construct([
            'status' => ResponseStatus::ERROR, 
            'message' => $message, 
            'data' => $data,
            'code' => $errorCode 
        ], $statusCode, $headers);
    }
} 