<?php

declare(strict_types=1);

namespace App\Repository\Filter\EntityFilter;

use Doctrine\ORM\QueryBuilder;

interface EntityFilterInterface {
    
    public function supports(mixed $value): bool;

    public function setQbIdentifier(string $qbIdentifier): static;

    public function apply(QueryBuilder $qb, mixed $value, string $filterId): void;
}