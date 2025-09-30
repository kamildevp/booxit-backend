<?php

declare(strict_types=1);

namespace App\Repository\Filter\EntityFilter;

use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

interface EntityFilterInterface {
    
    public function supports(mixed $value): bool;

    public function setQbIdentifier(string $qbIdentifier): static;

    public function setNormalizer(DenormalizerInterface&NormalizerInterface $normalizer): static;

    public function apply(QueryBuilder $qb, mixed $value, string $filterId): void;
}