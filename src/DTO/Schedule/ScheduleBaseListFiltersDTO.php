<?php

declare(strict_types=1);

namespace App\DTO\Schedule;

use App\DTO\ListFiltersDTO;
use App\Validator\Constraints\Compound as Compound;

class ScheduleBaseListFiltersDTO extends ListFiltersDTO 
{
    public function __construct(
        #[Compound\ContainsFilterRequirements] public readonly ?string $name = null,
    )
    {

    }
}