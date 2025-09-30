<?php

declare(strict_types=1);

namespace App\DTO\Trait;

use App\Validator\Constraints\Compound as Compound;
use OpenApi\Attributes as OA;

trait TimestampsFiltersFieldsDTO 
{
    #[OA\Property(format: 'date-time')]
    #[Compound\DateTimeStringRequirements(true)]
    public readonly ?string $createdFrom;

    #[OA\Property(format: 'date-time')]
    #[Compound\DateTimeStringRequirements(true)]
    public readonly ?string $createdTo;

    #[OA\Property(format: 'date-time')]
    #[Compound\DateTimeStringRequirements(true)]
    public readonly ?string $updatedFrom;

    #[OA\Property(format: 'date-time')]
    #[Compound\DateTimeStringRequirements(true)]
    public readonly ?string $updatedTo;
}