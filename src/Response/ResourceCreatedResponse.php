<?php

namespace App\Response;

class ResourceCreatedResponse extends SuccessResponse
{
    public const RESPONSE_STATUS = 201;

    public function __construct(mixed $resource = null, array $headers = [])
    {
        parent::__construct($resource, self::RESPONSE_STATUS, $headers);
    }
} 