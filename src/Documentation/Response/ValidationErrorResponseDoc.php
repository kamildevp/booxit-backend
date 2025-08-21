<?php

namespace App\Documentation\Response;

use App\Response\ValidationErrorResponse;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class ValidationErrorResponseDoc extends ClientErrorResponseDoc
{
    public function __construct(
        string $description = 'Validation Error Response', 
        ?array $errorsExample = null, 
        array $headers = []
    )
    {
        parent::__construct(
            statusCode: ValidationErrorResponse::RESPONSE_STATUS,
            message: ValidationErrorResponse::RESPONSE_MESSAGE,
            description: $description,
            errorsExample: $errorsExample,
            headers: $headers
        );
    }
}