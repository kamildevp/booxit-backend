<?php

declare(strict_types=1);

namespace App\DTO\Organization;

use App\DTO\AbstractDTO;
use App\DTO\Organization\Trait\OrganizationBaseFieldsDTO;

class OrganizationPatchDTO extends AbstractDTO 
{
    use OrganizationBaseFieldsDTO;

    public function __construct(string $name, string $description)
    {
        $this->name = $name;
        $this->description = $description;
    }
}