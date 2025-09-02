<?php

declare(strict_types=1);

namespace App\DTO;

interface PaginationDTOInterface
{
    public function getPage(): int;

    public function getPerPage(): int;
}