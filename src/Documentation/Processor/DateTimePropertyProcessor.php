<?php

declare(strict_types=1);

namespace App\Documentation\Processor;

use OpenApi\Analysis;
use OpenApi\Generator;

final class DateTimePropertyProcessor
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
                if ($property->format == 'date-time' && (Generator::isDefault($property->nullable) || !$property->nullable)) {
                    $property->example = '2025-06-13T12:20:00+00:00';
                }
                
            }
        }
    }
}
