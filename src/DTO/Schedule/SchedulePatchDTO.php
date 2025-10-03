<?php

declare(strict_types=1);

namespace App\DTO\Schedule;

use App\DTO\AbstractDTO;
use App\DTO\Schedule\Trait\ScheduleBaseFieldsDTO;

class SchedulePatchDTO extends AbstractDTO 
{
    use ScheduleBaseFieldsDTO;

    public function __construct(
        string $name, 
        string $description,
    )
    {
        $this->name = $name;
        $this->description = $description;
    }
}