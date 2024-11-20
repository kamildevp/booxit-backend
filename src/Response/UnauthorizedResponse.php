<?php

namespace App\Response;

use App\Enum\ResponseStatus;
use Symfony\Component\HttpFoundation\JsonResponse;

class UnauthorizedResponse extends JsonResponse
{
    public function __construct(array $headers = [])
    {
        parent::__construct([
            'status' => ResponseStatus::FAIL, 
            'data' => [
                'message' => 'Unauthorized'
            ]
        ], 401, $headers);
    }
} 