<?php

declare(strict_types=1);

namespace App\Repository\Filter\EntityFilter;

use Doctrine\ORM\QueryBuilder;

class FieldContains extends AbstractFieldFilter
{
    public function apply(QueryBuilder $qb, mixed $value, string $filterId): void
    {
        $qb->andWhere("LOWER(e.$this->propertyName) LIKE LOWER(:$filterId)")->setParameter($filterId, '%' . $value . '%');
    }
}