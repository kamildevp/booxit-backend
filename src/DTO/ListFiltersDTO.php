<?php

declare(strict_types=1);

namespace App\DTO;

use Nelmio\ApiDocBundle\Attribute\Ignore;

abstract class ListFiltersDTO extends AbstractDTO implements FiltersDTOInterface
{
    #[Ignore]
    public function getFilter(string $parameterName): mixed
    {
        return $this->{$parameterName} ?? null;
    }

    #[Ignore]
    public function hasFilter(string $parameterName): bool
    {
        return property_exists($this, $parameterName);
    }
}