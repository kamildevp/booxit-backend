<?php

namespace App\Response;

use App\Enum\ResponseStatus;
use Symfony\Component\HttpFoundation\JsonResponse;

class NotFoundResponse extends JsonResponse
{
    public function __construct(array $headers = [])
    {
        parent::__construct([
            'status' => ResponseStatus::FAIL, 
            'data' => [
                'message' => 'Not Found',
            ]
        ], 404, $headers);
    }
} 