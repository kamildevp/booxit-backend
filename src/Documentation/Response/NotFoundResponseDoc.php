<?php

declare(strict_types=1);

namespace App\Documentation\Response;

use App\Response\NotFoundResponse;
use OpenApi\Attributes as OA;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class NotFoundResponseDoc extends ClientErrorResponseDoc
{
    public function __construct(
        string $message = NotFoundResponse::RESPONSE_MESSAGE,
        string $description = 'Not Found Response',
        array $headers = [],
        ?array $messages = null
    )
    {
        $contentExamples = null;
        if(is_array($messages)){
            $contentExamples = [];
            foreach($messages as $exampleMessage){
                $contentExamples[] = new OA\Examples(summary: $exampleMessage,example: $exampleMessage,value: [
                    'status' => 'fail', 
                    'data' => [
                        'message' => $exampleMessage
                    ]
                ]);
            }
        }


        parent::__construct(
            statusCode: NotFoundResponse::RESPONSE_STATUS,
            message: $message,
            description: $description,
            headers: $headers,
            contentExamples: $contentExamples
        );
    }
}