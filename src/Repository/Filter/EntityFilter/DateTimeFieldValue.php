<?php

declare(strict_types=1);

namespace App\Repository\Filter\EntityFilter;

use App\Validator\Constraints\Compound\DateTimeStringRequirements;
use DateTimeImmutable;
use Doctrine\ORM\QueryBuilder;

class DateTimeFieldValue extends AbstractFieldFilter
{
    public function __construct(string $propertyName, protected string $operator)
    {
        parent::__construct($propertyName);
    }

    public function supports(mixed $value): bool
    {
        if(!is_string($value)){
            return false;
        }
        
        $dt = DateTimeImmutable::createFromFormat(DateTimeStringRequirements::FORMAT, $value);

        return $dt !== false && $dt->format(DateTimeStringRequirements::FORMAT) === $value;
    }

    public function apply(QueryBuilder $qb, mixed $value, string $filterId): void
    {
        $dt = $this->normalizer->denormalize($value, DateTimeImmutable::class);
        $qb->andWhere("$this->qbIdentifier.$this->propertyName $this->operator :$filterId")->setParameter($filterId, $dt);
    }
}