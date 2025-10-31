<?php

declare(strict_types=1);

namespace App\DTO;

use App\DTO\ListFiltersDTO;
use Symfony\Component\Validator\Constraints as Assert;

class LocationRadiusFilterDTO extends ListFiltersDTO 
{
    public function __construct(
        #[Assert\Range(min: -90, max: 90)]
        public readonly float $lat,
        #[Assert\Range(min: -180, max: 180)]
        public readonly float $lng,
        #[Assert\Range(min: 1, max: 100)]
        public readonly int $radius,
    )
    {

    }
}