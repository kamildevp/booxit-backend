<?php

declare(strict_types=1);

namespace App\Repository\Filter\EntityFilter;

use App\Repository\Filter\FiltersBuilder;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

interface EntityFilterInterface {
    
    public function supports(mixed $value): bool;

    public function setQbIdentifier(string $qbIdentifier): static;

    public function setNormalizer(DenormalizerInterface&NormalizerInterface $normalizer): static;

    public function setFiltersBuilder(FiltersBuilder $filtersBuilder): static;

    public function apply(QueryBuilder $qb, mixed $value, string $filterId): void;
}