<?php

namespace App\Response;

use App\Enum\ResponseStatus;
use Symfony\Component\HttpFoundation\JsonResponse;

class ResourceCreatedResponse extends JsonResponse
{
    public function __construct(mixed $resource = null, array $headers = [])
    {
        parent::__construct([
            'status' => ResponseStatus::SUCCESS, 
            'data' => $resource
        ], 201, $headers);
    }
} 