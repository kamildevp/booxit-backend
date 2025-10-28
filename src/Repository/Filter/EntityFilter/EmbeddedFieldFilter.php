<?php

declare(strict_types=1);

namespace App\Repository\Filter\EntityFilter;

use App\DTO\FiltersDTOInterface;
use Doctrine\ORM\QueryBuilder;

class EmbeddedFieldFilter extends AbstractFieldFilter
{
    public function __construct(string $propertyName, protected string $embeddedClass)
    {
        parent::__construct($propertyName);
    }

    public function supports(mixed $value): bool
    {
        return $value instanceof FiltersDTOInterface;
    }

    public function apply(QueryBuilder $qb, mixed $value, string $filterId): void
    {
        if(!$value instanceof FiltersDTOInterface){
            return;
        }

        $this->filtersBuilder->applyFilters($qb, $this->embeddedClass, $value, "$this->qbIdentifier.$this->propertyName");
    }
}