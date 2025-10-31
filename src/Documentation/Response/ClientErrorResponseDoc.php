<?php

declare(strict_types=1);

namespace App\Documentation\Response;

use App\Enum\ResponseStatus;
use OpenApi\Attributes as OA;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class ClientErrorResponseDoc extends OA\Response
{
    public function __construct(
        int $statusCode = 400, 
        ?string $message = null,
        ?string $description = null, 
        mixed $errorsExample = null, 
        array $headers = [],
        ?array $contentExamples = null,
    )
    {
        $dataProperty =  new OA\Property(property: 'data', type: 'object', properties: [
                new OA\Property(property: "message", type: "string", example: $message),
                new OA\Property(property: 'errors', type: 'object', example: $errorsExample),
        ]);

        $content = new OA\JsonContent(
            type: "object",
            properties: [
                new OA\Property(property: "status", type: "string", example: ResponseStatus::FAIL->value),
                $dataProperty
            ],
            examples: $contentExamples
        );

        parent::__construct( 
            response: $statusCode,
            description: $description,
            content: $content,
            headers: $headers
        );
    }
}