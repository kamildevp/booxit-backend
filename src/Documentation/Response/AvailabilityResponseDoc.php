<?php

declare(strict_types=1);

namespace App\Documentation\Response;

use OpenApi\Attributes as OA;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class AvailabilityResponseDoc extends SuccessResponseDoc
{
    public function __construct(
        ?string $description = 'Availability per date', 
        array $headers = []
    )
    {
        $dates = ['2025-10-10', '2025-12-24'];
        $properties = [];
        foreach($dates as $date){
            $properties[] = new OA\Property(
                    $date, 
                    type: 'array', 
                    items: new OA\Items(
                        type: 'string',
                        format: 'time', 
                    ),
                    example: ['09:00', '09:15', '11:00']
            );
        }

        $dataContent = new OA\JsonContent(type: 'object', properties: $properties);

        parent::__construct(
            description: $description,
            dataContent: $dataContent,
            headers: $headers
        );
    }
}