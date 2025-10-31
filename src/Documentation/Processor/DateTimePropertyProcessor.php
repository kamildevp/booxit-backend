<?php

declare(strict_types=1);

namespace App\Documentation\Processor;

use App\Validator\Constraints\Compound\DateTimeStringRequirements;
use DateTimeImmutable;
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
                    $property->example = (new DateTimeImmutable('monday next week'))->setTime(12,0)->format(DateTimeStringRequirements::FORMAT);
                }
                
            }
        }
    }
}
