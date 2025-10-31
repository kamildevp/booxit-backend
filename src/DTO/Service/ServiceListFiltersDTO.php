<?php

declare(strict_types=1);

namespace App\DTO\Service;

use App\DTO\ListFiltersDTO;
use App\DTO\Trait\TimestampsFiltersFieldsDTO;
use App\Enum\Service\ServiceCategory;
use App\Validator\Constraints\Compound as Compound;
use OpenApi\Attributes as OA;

class ServiceListFiltersDTO extends ListFiltersDTO 
{
    use TimestampsFiltersFieldsDTO;

    public function __construct(
        #[Compound\ContainsFilterRequirements] 
        public readonly ?string $name = null,
        #[Compound\DateIntervalRequirements(true)] 
        public readonly ?string $durationFrom = null,
        #[Compound\DateIntervalRequirements(true)] 
        public readonly ?string $durationTo = null,
        #[Compound\DecimalRequirements(true)] 
        public readonly ?string $estimatedPriceFrom = null,
        #[Compound\DecimalRequirements(true)] 
        public readonly ?string $estimatedPriceTo = null,
        #[OA\Property(type: 'array', items: new OA\Items(type: 'string'))]
        #[Compound\EnumSetRequirements(ServiceCategory::class)] 
        public readonly ?array $category = null,
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