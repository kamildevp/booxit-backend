<?php

namespace App\Response;

use App\Enum\ResponseStatus;
use Symfony\Component\HttpFoundation\JsonResponse;

class ResourceCreatedResponse extends JsonResponse
{
    public const RESPONSE_STATUS = 201;

    public function __construct(mixed $resource = null, array $headers = [])
    {
        parent::__construct([
            'status' => ResponseStatus::SUCCESS, 
            'data' => $resource
        ], self::RESPONSE_STATUS, $headers);
    }
} 