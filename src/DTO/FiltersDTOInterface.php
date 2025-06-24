<?php

namespace App\DTO;

interface FiltersDTOInterface {
    public function getFilter(string $parameterName): mixed;

    public function hasFilter(string $parameterName): bool;
}