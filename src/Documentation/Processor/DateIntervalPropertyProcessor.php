<?php

declare(strict_types=1);

namespace App\Documentation\Processor;

use OpenApi\Analysis;
use OpenApi\Generator;

final class DateIntervalPropertyProcessor
{
    public function __invoke(Analysis $analysis): void
    {
        if (Generator::isDefault($analysis->openapi->components) || Generator::isDefault($analysis->openapi->components->schemas)) {
            return;
        }

        $schemas = $analysis->openapi->components->schemas;

        foreach ($schemas as $schema) {
            if (Generator::isDefault($schema->properties)) {
                continue;
            }

            foreach ($schema->properties as $property) {
                if ($property->ref == '#/components/schemas/DateInterval') {
                    $property->type = 'string';
                    $property->example = Generator::isDefault($property->example) ? 'PT01H30M' : $property->example;
                    $property->ref = Generator::UNDEFINED;
                }
                
            }
        }

        $analysis->openapi->components->schemas = array_filter($schemas, fn($schema) => $schema->schema != 'DateInterval');
    }
}
