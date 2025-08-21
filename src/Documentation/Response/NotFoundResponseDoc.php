<?php

namespace App\Documentation\Response;

use App\Response\NotFoundResponse;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class NotFoundResponseDoc extends ClientErrorResponseDoc
{
    public function __construct(
        string $message = NotFoundResponse::RESPONSE_MESSAGE,
        string $description = 'Not Found Response',
        array $headers = []
    )
    {
        parent::__construct(
            statusCode: NotFoundResponse::RESPONSE_STATUS,
            message: $message,
            description: $description,
            headers: $headers
        );
    }
}