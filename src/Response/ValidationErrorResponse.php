<?php

namespace App\Response;

use App\Enum\ResponseStatus;
use Symfony\Component\HttpFoundation\JsonResponse;

class ValidationErrorResponse extends JsonResponse
{
    public function __construct(mixed $errors = null, array $headers = [])
    {
        parent::__construct([
            'status' => ResponseStatus::FAIL, 
            'data' => [
                'message' => 'Validation Error',
                'errors' => $errors
            ]
        ], 422, $headers);
    }
} 