<?php

namespace App\Repository\Order\EntityOrder;

use Doctrine\ORM\QueryBuilder;

class BaseFieldOrder extends AbstractFieldOrder
{
    public function apply(QueryBuilder $qb, string $columnName, string $dir, string $orderId): void
    {
        $qb->addOrderBy("e.$columnName", $dir);
    }
}