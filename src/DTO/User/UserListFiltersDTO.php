<?php

namespace App\DTO\User;

use App\DTO\ListFiltersDTO;
use Symfony\Component\Validator\Constraints as Assert;

class UserListFiltersDTO extends ListFiltersDTO {

    public function __construct(
        #[Assert\Length(
            min: 1,
            max: 50,
            minMessage: 'Parameter must be at least {{ limit }} characters long',
            maxMessage: 'Parameter cannot be longer than {{ limit }} characters',
        )]
        public ?string $name = null,
    )
    {
        
    }
}