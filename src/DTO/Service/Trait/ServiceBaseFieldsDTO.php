<?php

declare(strict_types=1);

namespace App\DTO\Service\Trait;

use App\Enum\Service\ServiceCategory;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\Constraints\Compound as Compound;
use App\Validator\Constraints as CustomAssert;
use OpenApi\Attributes as OA; 

trait ServiceBaseFieldsDTO
{
    #[Compound\NameRequirements]
    public readonly string $name;

    #[Assert\Choice(callback: [ServiceCategory::class, 'values'], message: 'Parameter must be one of valid categories: {{ choices }}')]
    public readonly string $category; 

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

    #[OA\Property(example: 5)]
    #[Assert\Range(min: 0, max: 525600)]
    public readonly int $availabilityOffset;
}