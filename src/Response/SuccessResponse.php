<?php

namespace App\Response;

use App\Enum\ResponseStatus;
use Symfony\Component\HttpFoundation\JsonResponse;

class SuccessResponse extends JsonResponse
{
    public function __construct(mixed $data = null, int $statusCode = 200, array $headers = [])
    {
        parent::__construct([
            'status' => ResponseStatus::SUCCESS, 
            'data' => $data
        ], $statusCode, $headers);
    }
} 