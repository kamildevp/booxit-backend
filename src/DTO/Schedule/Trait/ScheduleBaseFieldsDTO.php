<?php

declare(strict_types=1);

namespace App\DTO\Schedule\Trait;

use App\Validator\Constraints\Compound as Compound;

trait ScheduleBaseFieldsDTO
{
    #[Compound\NameRequirements]
    public readonly string $name;

    #[Compound\DescriptionRequirements]
    public readonly string $description;
}