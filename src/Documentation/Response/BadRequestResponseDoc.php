<?php

namespace App\Documentation\Response;

use App\Response\BadRequestResponse;
use OpenApi\Generator;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class BadRequestResponseDoc extends ClientErrorResponseDoc
{
    public function __construct(
        string $description = 'Bad Request Response',
        mixed $errorsExample = Generator::UNDEFINED, 
        array $headers = []
    )
    {
        parent::__construct(
            statusCode: BadRequestResponse::RESPONSE_STATUS,
            message: BadRequestResponse::RESPONSE_MESSAGE,
            description: $description,
            errorsExample: $errorsExample,
            headers: $headers
        );
    }
}