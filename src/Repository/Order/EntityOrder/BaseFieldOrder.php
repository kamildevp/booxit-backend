<?php

declare(strict_types=1);

namespace App\Repository\Order\EntityOrder;

use Doctrine\ORM\QueryBuilder;

class BaseFieldOrder extends AbstractFieldOrder
{
    public function apply(QueryBuilder $qb, string $dir, string $orderId): void
    {
        $qb->addOrderBy("$this->qbIdentifier.$this->propertyName", $dir);
    }
}