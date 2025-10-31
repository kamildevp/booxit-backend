<?php

declare(strict_types=1);

namespace App\DTO\Trait;

use Symfony\Component\Validator\Constraints as Assert;

trait PaginationFieldsDTO 
{
    #[Assert\GreaterThan(0)]
    public readonly int $page;

    #[Assert\GreaterThan(0)]
    public readonly int $perPage;

    public function getPage(): int
    {
        return $this->page;
    }

    public function getPerPage(): int
    {
        return $this->perPage;
    }
}