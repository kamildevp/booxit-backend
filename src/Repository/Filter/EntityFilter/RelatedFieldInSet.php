<?php

declare(strict_types=1);

namespace App\Repository\Filter\EntityFilter;

use Doctrine\ORM\QueryBuilder;

class RelatedFieldInSet extends AbstractRelatedFieldFilter
{
    public function __construct(string $relationName, string $propertyName, private bool $not = false)
    {
        parent::__construct($relationName, $propertyName);
    }

    public function supports(mixed $value): bool
    {
        return is_array($value);
    }

    public function apply(QueryBuilder $qb, mixed $value, string $filterId): void
    {
        $operator = $this->not ? 'NOT IN' : 'IN';
        $relationQbIdentifier = $this->joinRelation($qb, $filterId);
        $qb->andWhere("$relationQbIdentifier.$this->propertyName $operator (:$filterId)")->setParameter($filterId, $value);
    }
}