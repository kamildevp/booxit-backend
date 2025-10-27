<?php

declare(strict_types=1);

namespace App\Repository\Filter\EntityFilter;

use Doctrine\ORM\QueryBuilder;

abstract class AbstractRelatedFieldFilter extends AbstractFieldFilter
{
    public function __construct(protected string $relationName, string $propertyName)
    {
        parent::__construct($propertyName);
    }

    protected function joinRelation(QueryBuilder $qb, string $filterId): string
    {
        $relationQbIdentifier = "rf$filterId";
        $qb->leftJoin("$this->qbIdentifier.$this->relationName", $relationQbIdentifier);

        return $relationQbIdentifier;
    }
}