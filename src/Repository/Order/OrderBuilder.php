<?php

namespace App\Repository\Order;

use App\DTO\OrderDTOInterface;
use App\Repository\Order\EntityOrder\Attribute\Order;
use App\Repository\Order\EntityOrder\EntityOrderInterface;
use Doctrine\ORM\QueryBuilder;
use ReflectionClass;
use ReflectionProperty;

class OrderBuilder {

    public function applyOrder(QueryBuilder $qb, string $entityClass, OrderDTOInterface $orderDTO, array $defaultOrderMap): void
    {
        $availableOrderDefs = $this->getAvailableOrderDefs($entityClass);
        $orderMap = $orderDTO->getOrderMap();
        $orderMap = empty($orderMap) ? $defaultOrderMap : $orderMap;

        $orderIndx = 0;
        foreach($orderMap as $parameterName => $orderDir)
        {
            $orderDef = array_key_exists($parameterName, $availableOrderDefs) ? $availableOrderDefs[$parameterName] : null;
            $order = $orderDef['order'];
            if(!$order instanceof EntityOrderInterface){
                continue;
            }

            $order->apply($qb, $orderDef['propertyName'], $orderDir, "orderParam$orderIndx");
            $orderIndx++;
        }
    }

    public function getAvailableOrderDefs(string $entityClass): array
    {
        $entityReflection = new ReflectionClass($entityClass);
        $availableOrders = [];

        foreach($entityReflection->getProperties() as $property){
            $orders = $this->getPropertyOrders($property);
            
            foreach($orders as $parameterName => $order){
                if(!$order instanceof EntityOrderInterface){
                    continue;
                }

                $availableOrders[$parameterName] = [
                    'propertyName' => $property->getName(),
                    'order' => $order
                ];
            }
        }
        return $availableOrders;
    }

    private function getPropertyOrders(ReflectionProperty $property): array
    {        
        $orders = [];
        foreach($property->getAttributes(Order::class) as $orderAttribute){
            $orderAttributeInstance = $orderAttribute->newInstance();
            $orders[$orderAttributeInstance->parameterName] = $orderAttributeInstance->entityOrder;
        }

        return $orders;
    }
}