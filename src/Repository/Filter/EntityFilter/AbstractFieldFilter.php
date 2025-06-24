<?php

namespace App\Repository\Filter\EntityFilter;

use Doctrine\ORM\QueryBuilder;

abstract class AbstractFieldFilter implements EntityFilterInterface
{
    public function supports(mixed $value): bool
    {
        return is_string($value);
    }

    abstract public function apply(QueryBuilder $qb, string $columnName, mixed $value, string $filterId): void;
}