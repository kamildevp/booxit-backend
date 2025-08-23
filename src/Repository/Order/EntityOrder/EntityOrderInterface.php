<?php

declare(strict_types=1);

namespace App\Repository\Order\EntityOrder;

use Doctrine\ORM\QueryBuilder;

interface EntityOrderInterface 
{
    public function apply(QueryBuilder $qb, string $columnName, string $dir, string $orderId): void;
}