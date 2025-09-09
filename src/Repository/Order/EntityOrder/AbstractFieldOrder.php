<?php

declare(strict_types=1);

namespace App\Repository\Order\EntityOrder;

use Doctrine\ORM\QueryBuilder;

abstract class AbstractFieldOrder implements EntityOrderInterface
{
    protected string $qbIdentifier = 'e';

    public function __construct(protected string $propertyName)
    {
        
    }

    public function setQbIdentifier(string $qbIdentifier): static
    {
        $this->qbIdentifier = $qbIdentifier;
        return $this;
    }

    abstract public function apply(QueryBuilder $qb, string $dir, string $orderId): void;
}