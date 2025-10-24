<?php

declare(strict_types=1);

namespace App\Documentation\Response;

use App\Response\NoContentResponse;
use OpenApi\Attributes as OA;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
class NoContentResponseDoc extends OA\Response
{
    public function __construct(
        string $description = 'No content', 
        array $headers = []
    )
    { 
        parent::__construct(
            response: NoContentResponse::RESPONSE_STATUS,
            description: $description,
            content: null,
            headers: $headers
        );
    }
}