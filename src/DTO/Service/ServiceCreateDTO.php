<?php

declare(strict_types=1);

namespace App\DTO\Service;

use App\DTO\AbstractDTO;
use App\DTO\Service\Trait\ServiceBaseFieldsDTO;

class ServiceCreateDTO extends AbstractDTO 
{
    use ServiceBaseFieldsDTO;

    public function __construct(
        string $name,
        string $category,
        string $description,
        string $duration,
        string $estimatedPrice
    )
    {
        $this->name = $name;
        $this->category = $category;
        $this->description = $description;
        $this->duration = $duration;
        $this->estimatedPrice = $estimatedPrice;
    }
}