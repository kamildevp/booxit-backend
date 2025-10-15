<?php

declare(strict_types=1);

namespace App\DTO\Service\Trait;

use App\Validator\Constraints\Compound as Compound;
use App\Validator\Constraints as CustomAssert;
use OpenApi\Attributes as OA; 

trait ServiceBaseFieldsDTO
{
    #[Compound\NameRequirements]
    public readonly string $name;

    #[Compound\DescriptionRequirements]
    public readonly string $description; 

    #[OA\Property(example: 'PT01H30M')]
    #[CustomAssert\DateIntervalLength(
        'PT10M', 
        'P1D', 
        'Duration cannot be shorter than 10 minutes.',
        'Duration cannot be longer than 1 day.'
    )]
    #[Compound\DateIntervalRequirements]
    public readonly string $duration; 

    #[OA\Property(example: '25.50')]
    #[Compound\DecimalRequirements]
    public readonly string $estimatedPrice;
}