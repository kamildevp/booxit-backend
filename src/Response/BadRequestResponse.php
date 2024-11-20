<?php

namespace App\Response;

use App\Enum\ResponseStatus;
use Symfony\Component\HttpFoundation\JsonResponse;

class BadRequestResponse extends JsonResponse
{
    public function __construct(mixed $errors = null, array $headers = [])
    {
        parent::__construct([
            'status' => ResponseStatus::FAIL, 
            'data' => [
                'message' => 'Bad Request',
                'errors' => $errors
            ]
        ], 400, $headers);
    }
} 