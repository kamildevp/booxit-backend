<?php

declare(strict_types=1);

namespace App\Documentation\Response;

use App\Response\UnauthorizedResponse;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class UnauthorizedResponseDoc extends ClientErrorResponseDoc
{
    public function __construct(
        string $message = UnauthorizedResponse::RESPONSE_MESSAGE,
        string $description = 'Unauthorized Response',
        array $headers = []
    )
    {
        parent::__construct(
            statusCode: UnauthorizedResponse::RESPONSE_STATUS,
            message: $message,
            description: $description,
            headers: $headers
        );
    }
}