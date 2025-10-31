<?php

declare(strict_types=1);

namespace App\Repository\Filter\EntityFilter;

use Doctrine\ORM\QueryBuilder;

class FieldInSet extends AbstractFieldFilter
{
    public function __construct(string $propertyName, private bool $not = false)
    {
        parent::__construct($propertyName);
    }

    public function supports(mixed $value): bool
    {
        return is_array($value);
    }

    public function apply(QueryBuilder $qb, mixed $value, string $filterId): void
    {
        $operator = $this->not ? 'NOT IN' : 'IN';
        $qb->andWhere("$this->qbIdentifier.$this->propertyName $operator (:$filterId)")->setParameter($filterId, $value);
    }
}