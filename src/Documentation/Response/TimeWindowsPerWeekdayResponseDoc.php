<?php

declare(strict_types=1);

namespace App\Documentation\Response;

use App\Enum\Weekday;
use OpenApi\Attributes as OA;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class TimeWindowsPerWeekdayResponseDoc extends SuccessResponseDoc
{
    public function __construct(
        ?string $description = 'Time windows per weekday', 
        array $headers = []
    )
    {
        $properties = [];
        foreach(Weekday::values() as $weekday){
            $properties[] = new OA\Property(
                    $weekday, 
                    type: 'array', 
                    items: new OA\Items(
                        type: 'object',
                        properties: [
                            new OA\Property('start_time', type: 'string', format: 'time', example: '09:00'),
                            new OA\Property('end_time', type: 'string', format: 'time', example: '17:00')
                        ]
                    )
            );
        }

        $properties[] = new OA\Property(
                'timezone', 
                type: 'string', 
                example: 'Europe/Warsaw' 
        );

        $dataContent = new OA\JsonContent(type: 'object', properties: $properties);

        parent::__construct(
            description: $description,
            dataContent: $dataContent,
            headers: $headers
        );
    }
}