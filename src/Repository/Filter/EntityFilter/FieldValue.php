<?php

namespace App\Repository\Filter\EntityFilter;

use Doctrine\ORM\QueryBuilder;

class FieldValue extends AbstractFieldFilter
{
    public function __construct(public string $operator)
    {
        
    }

    public function apply(QueryBuilder $qb, string $columnName, mixed $value, string $filterId): void
    {
        $qb->andWhere("e.$columnName $this->operator :$filterId")->setParameter($filterId, $value);
    }
}