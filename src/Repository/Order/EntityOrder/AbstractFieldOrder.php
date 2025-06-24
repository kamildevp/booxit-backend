<?php

namespace App\Repository\Order\EntityOrder;

use Doctrine\ORM\QueryBuilder;

abstract class AbstractFieldOrder implements EntityOrderInterface
{
    abstract public function apply(QueryBuilder $qb, string $columnName, string $dir, string $orderId): void;
}