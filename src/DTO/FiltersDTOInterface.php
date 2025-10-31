<?php

declare(strict_types=1);

namespace App\DTO;

interface FiltersDTOInterface 
{
    public function getFilter(string $parameterName): mixed;

    public function hasFilter(string $parameterName): bool;
}