<?php

declare(strict_types=1);

namespace App\DTO\Schedule;

use App\DTO\ListFiltersDTO;
use App\DTO\Trait\TimestampsFiltersFieldsDTO;
use App\Validator\Constraints\Compound as Compound;

class ScheduleListFiltersDTO extends ListFiltersDTO 
{
    use TimestampsFiltersFieldsDTO;

    public function __construct(
        #[Compound\ContainsFilterRequirements] public readonly ?string $name = null,
        ?string $createdFrom = null,
        ?string $createdTo = null,
        ?string $updatedFrom = null,
        ?string $updatedTo = null,
    )
    {
        $this->createdFrom = $createdFrom;
        $this->createdTo = $createdTo;
        $this->updatedFrom = $updatedFrom;
        $this->updatedTo = $updatedTo;
    }
}