<?php

namespace App\Documentation\Response;

use App\Response\ForbiddenResponse;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class ForbiddenResponseDoc extends ClientErrorResponseDoc
{
    public function __construct(
        string $message = ForbiddenResponse::RESPONSE_MESSAGE,
        string $description = 'Forbidden Response',
        array $headers = []
    )
    {
        parent::__construct(
            statusCode: ForbiddenResponse::RESPONSE_STATUS,
            message: $message,
            description: $description,
            headers: $headers
        );
    }
}