<?php

declare(strict_types=1);

namespace App\DTO\WorkingHours;

use App\DTO\AbstractDTO;
use App\Validator\Constraints\Compound as Compound;
use App\Validator\Constraints as CustomAssert;

#[CustomAssert\TimeWindow]
class TimeWindowDTO extends AbstractDTO 
{
    public function __construct(
        #[Compound\QuarterHourTimeRequirements]
        public readonly string $startTime,
        #[Compound\QuarterHourTimeRequirements]
        public readonly string $endTime
    )
    {

    }
}