<?php

namespace App\DTO;

abstract class ListFiltersDTO extends AbstractDTO implements FiltersDTOInterface
{
    public function getFilter(string $parameterName): mixed
    {
        return $this->{$parameterName} ?? null;
    }

    public function hasFilter(string $parameterName): bool
    {
        return property_exists($this, $parameterName);
    }
}