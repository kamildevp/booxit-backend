<?php

namespace App\Documentation\Response;

use App\Enum\ResponseStatus;
use App\Response\ServerErrorResponse;
use OpenApi\Attributes as OA;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class ServerErrorResponseDoc extends OA\Response
{
    public function __construct(
        int $statusCode = ServerErrorResponse::RESPONSE_STATUS, 
        ?string $message = ServerErrorResponse::RESPONSE_MESSAGE,
        ?int $errorCode = null,
        ?string $description = 'Server Error Response', 
        ?OA\JsonContent $dataContent = null, 
        array $headers = []
    )
    {
        $dataProperty =  new OA\Property(property: 'data', type: 'object', properties: $dataContent->properties ?? [], example: null);

        $content = new OA\JsonContent(
            type: "object",
            properties: [
                new OA\Property(property: "status", type: "string", example: ResponseStatus::ERROR->value),
                new OA\Property(property: "message", type: "string", example: $message),
                $dataProperty,
                new OA\Property(property: "error_code", type: "integer", example: $errorCode),
            ]
        );

        parent::__construct(
            response: $statusCode,
            description: $description,
            content: $content,
            headers: $headers
        );
    }
}