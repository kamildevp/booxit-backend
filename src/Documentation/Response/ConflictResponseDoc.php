<?php

declare(strict_types=1);

namespace App\Documentation\Response;

use App\Response\ConflictResponse;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class ConflictResponseDoc extends ClientErrorResponseDoc
{
    public function __construct(
        string $message = ConflictResponse::RESPONSE_MESSAGE,
        string $description = 'Conflict Response',
        array $headers = []
    )
    {
        parent::__construct(
            statusCode: ConflictResponse::RESPONSE_STATUS,
            message: $message,
            description: $description,
            headers: $headers
        );
    }
}