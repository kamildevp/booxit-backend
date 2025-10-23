<?php

declare(strict_types=1);

namespace App\Repository\Filter\EntityFilter;

use DateTimeInterface;
use Doctrine\ORM\QueryBuilder;

class FieldValue extends AbstractFieldFilter
{
    public function __construct(string $propertyName, protected string $operator)
    {
        parent::__construct($propertyName);
    }

    public function supports(mixed $value): bool
    {
        return is_string($value) || is_numeric($value) || is_bool($value) || $value instanceof DateTimeInterface;
    }

    public function apply(QueryBuilder $qb, mixed $value, string $filterId): void
    {
        $qb->andWhere("$this->qbIdentifier.$this->propertyName $this->operator :$filterId")->setParameter($filterId, $value);
    }
}