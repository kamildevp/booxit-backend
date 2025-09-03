<?php

declare(strict_types=1);

namespace App\Repository\Filter\EntityFilter;

use Doctrine\ORM\QueryBuilder;

abstract class AbstractFieldFilter implements EntityFilterInterface
{
    public function __construct(protected $propertyName)
    {
        
    }

    public function supports(mixed $value): bool
    {
        return is_string($value);
    }

    abstract public function apply(QueryBuilder $qb, mixed $value, string $filterId): void;
}