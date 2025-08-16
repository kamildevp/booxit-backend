<?php

namespace App\DTO\User;

use App\DTO\ListFiltersDTO;
use App\DTO\Trait\TimestampsFiltersFieldsDTO;
use DateTimeImmutable;
use Symfony\Component\Validator\Constraints as Assert;

class UserListFiltersDTO extends ListFiltersDTO 
{
    use TimestampsFiltersFieldsDTO;

    public function __construct(
        #[Assert\Length(
            min: 1,
            max: 50,
            minMessage: 'Parameter must be at least {{ limit }} characters long',
            maxMessage: 'Parameter cannot be longer than {{ limit }} characters',
        )]
        public readonly ?string $name = null,
        ?DateTimeImmutable $createdFrom = null,
        ?DateTimeImmutable $createdTo = null,
        ?DateTimeImmutable $updatedFrom = null,
        ?DateTimeImmutable $updatedTo = null,
    )
    {
        $this->createdFrom = $createdFrom;
        $this->createdTo = $createdTo;
        $this->updatedFrom = $updatedFrom;
        $this->updatedTo = $updatedTo;
    }
}