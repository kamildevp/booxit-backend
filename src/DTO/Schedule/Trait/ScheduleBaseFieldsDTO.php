<?php

declare(strict_types=1);

namespace App\DTO\Schedule\Trait;

use App\Validator\Constraints\Compound as Compound;
use Symfony\Component\Validator\Constraints as Assert;
use OpenApi\Attributes as OA;

trait ScheduleBaseFieldsDTO
{
    #[Compound\NameRequirements]
    public readonly string $name;

    #[Compound\DescriptionRequirements]
    public readonly string $description;

    #[OA\Property(example: 15)]
    #[Assert\Range(min: 5, max: 60)]
    public readonly int $division;

    #[OA\Property(example: 'Europe/Warsaw')]
    #[Assert\NotBlank]
    #[Assert\Timezone]
    public readonly string $timezone;
}