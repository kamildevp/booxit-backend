<?php

namespace App\Repository\Filter\EntityFilter;

use Doctrine\ORM\QueryBuilder;

class FieldContains extends AbstractFieldFilter
{
    public function apply(QueryBuilder $qb, string $columnName, mixed $value, string $filterId): void
    {
        $qb->andWhere("LOWER(e.$columnName) LIKE LOWER(:$filterId)")->setParameter($filterId, '%' . $value . '%');
    }
}