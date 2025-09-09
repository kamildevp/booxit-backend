<?php

declare(strict_types=1);

namespace App\Repository\Order\EntityOrder;

use Doctrine\ORM\QueryBuilder;

interface EntityOrderInterface 
{
    public function setQbIdentifier(string $qbIdentifier): static;

    public function apply(QueryBuilder $qb, string $dir, string $orderId): void;
}