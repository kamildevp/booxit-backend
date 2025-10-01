<?php

declare(strict_types=1);

namespace App\DTO\User;

use App\DTO\Trait\TimestampsFiltersFieldsDTO;

class UserListFiltersDTO extends UserBaseListFiltersDTO
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