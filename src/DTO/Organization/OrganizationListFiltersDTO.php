<?php

declare(strict_types=1);

namespace App\DTO\Organization;

use App\DTO\AddressFiltersDTO;
use App\DTO\Trait\TimestampsFiltersFieldsDTO;
use App\Enum\Service\ServiceCategory;
use App\Validator\Constraints\Compound as Compound;
use Symfony\Component\Validator\Constraints as Assert;

class OrganizationListFiltersDTO extends OrganizationBaseListFiltersDTO
{
    use TimestampsFiltersFieldsDTO;

    public function __construct(
        ?string $name = null,
        #[Assert\Valid]
        public readonly ?AddressFiltersDTO $address = null,
        #[Compound\EnumSetRequirements(ServiceCategory::class)] 
        public readonly ?array $serviceCategory = null,
        ?string $createdFrom = null,
        ?string $createdTo = null,
        ?string $updatedFrom = null,
        ?string $updatedTo = null,
    )
    {
        parent::__construct($name);
        $this->createdFrom = $createdFrom;
        $this->createdTo = $createdTo;
        $this->updatedFrom = $updatedFrom;
        $this->updatedTo = $updatedTo;
    }
}