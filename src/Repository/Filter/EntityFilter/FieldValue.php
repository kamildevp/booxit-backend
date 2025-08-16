<?php

namespace App\Repository\Filter\EntityFilter;

use DateTimeInterface;
use Doctrine\ORM\QueryBuilder;

class FieldValue extends AbstractFieldFilter
{
    public function __construct(public string $operator)
    {
        
    }

    public function supports(mixed $value): bool
    {
        return is_string($value) || is_numeric($value) || $value instanceof DateTimeInterface;
    }

    public function apply(QueryBuilder $qb, string $columnName, mixed $value, string $filterId): void
    {
        $qb->andWhere("e.$columnName $this->operator :$filterId")->setParameter($filterId, $value);
    }
}