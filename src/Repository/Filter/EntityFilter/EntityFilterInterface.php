<?php

namespace App\Repository\Filter\EntityFilter;

use Doctrine\ORM\QueryBuilder;

interface EntityFilterInterface {
    
    public function supports(mixed $value): bool;

    public function apply(QueryBuilder $qb, string $columnName, mixed $value, string $filterId): void;
}