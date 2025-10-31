<?php

declare(strict_types=1);

namespace App\DTO\User;

use App\DTO\ListFiltersDTO;
use App\Validator\Constraints\Compound as Compound;

class UserBaseListFiltersDTO extends ListFiltersDTO 
{
    public function __construct(
        #[Compound\ContainsFilterRequirements]
        public readonly ?string $name = null,
        #[Compound\ContainsFilterRequirements]
        public readonly ?string $username = null,
    )
    {

    }
}