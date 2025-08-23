<?php

declare(strict_types=1);

namespace App\Repository\Order\EntityOrder\Attribute;

use Attribute;
use App\Repository\Order\EntityOrder\EntityOrderInterface;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Order
{

    public function __construct(
        public string $parameterName,
        public EntityOrderInterface $entityOrder,
    )
    {
        
    }
}