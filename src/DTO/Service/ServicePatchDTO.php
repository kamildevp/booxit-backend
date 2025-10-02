<?php

declare(strict_types=1);

namespace App\DTO\Service;

use App\DTO\AbstractDTO;
use App\DTO\Service\Trait\ServiceBaseFieldsDTO;

class ServicePatchDTO extends AbstractDTO 
{
    use ServiceBaseFieldsDTO;

    public function __construct(
        string $name, 
        string $description, 
        string $duration, 
        string $estimatedPrice
    )
    {
        $this->name = $name;
        $this->description = $description;
        $this->duration = $duration;
        $this->estimatedPrice = $estimatedPrice;
    }
}