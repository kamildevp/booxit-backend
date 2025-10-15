<?php

declare(strict_types=1);

namespace App\DTO\WorkingHours;

use App\DTO\AbstractDTO;
use App\Validator\Constraints\Compound as Compound;
use App\Validator\Constraints as CustomAssert;

#[CustomAssert\TimeWindowLength(minLength: '10 minutes')]
class TimeWindowDTO extends AbstractDTO 
{
    public function __construct(
        #[Compound\TimeRequirements]
        public readonly string $startTime,
        #[Compound\TimeRequirements]
        public readonly string $endTime
    )
    {

    }
}