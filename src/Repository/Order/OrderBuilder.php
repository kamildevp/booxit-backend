<?php

declare(strict_types=1);

namespace App\Repository\Order;

use App\DTO\OrderDTOInterface;;
use App\Repository\Order\EntityOrder\EntityOrderInterface;
use Doctrine\ORM\QueryBuilder;

class OrderBuilder 
{
    const ORDER_DEFS_METHOD_NAME = 'getOrderDefs';

    public function applyOrder(QueryBuilder $qb, string $entityClass, OrderDTOInterface $orderDTO, array $defaultOrderMap): void
    {
        if(!method_exists($entityClass, self::ORDER_DEFS_METHOD_NAME)){
            return;
        }

        $availableOrderDefs = $entityClass::{self::ORDER_DEFS_METHOD_NAME}();
        $orderMap = $orderDTO->getOrderMap();
        $orderMap = empty($orderMap) ? $defaultOrderMap : $orderMap;

        $orderIndx = 0;
        foreach($orderMap as $parameterName => $orderDir)
        {
            $order = array_key_exists($parameterName, $availableOrderDefs) ? $availableOrderDefs[$parameterName] : null;
            if(!$order instanceof EntityOrderInterface){
                continue;
            }

            $order->apply($qb, $orderDir, "orderParam$orderIndx");
            $orderIndx++;
        }
    }
}