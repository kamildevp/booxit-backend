<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class PaginationDTO extends AbstractDTO {

    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Type('int')]
        #[Assert\GreaterThan(0)]
        public int $page = 1,

        #[Assert\NotBlank]
        #[Assert\Type('int')]
        #[Assert\GreaterThan(0)]
        public int $perPage = 20,
    )
    {
        
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getPerPage(): int
    {
        return $this->perPage;
    }
}