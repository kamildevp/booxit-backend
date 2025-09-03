<?php

declare(strict_types=1);

namespace App\Repository\Order\EntityOrder;

use Doctrine\ORM\QueryBuilder;

abstract class AbstractFieldOrder implements EntityOrderInterface
{
    public function __construct(protected string $propertyName)
    {
        
    }

    abstract public function apply(QueryBuilder $qb, string $dir, string $orderId): void;
}