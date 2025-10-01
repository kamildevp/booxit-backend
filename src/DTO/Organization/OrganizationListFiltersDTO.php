<?php

declare(strict_types=1);

namespace App\DTO\Organization;

use App\DTO\Trait\TimestampsFiltersFieldsDTO;

class OrganizationListFiltersDTO extends OrganizationBaseListFiltersDTO
{
    use TimestampsFiltersFieldsDTO;

    public function __construct(
        ?string $name = null,
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