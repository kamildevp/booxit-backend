<?php

declare(strict_types=1);

namespace App\Repository\Order;

use App\DTO\OrderDTOInterface;;
use App\Repository\Order\EntityOrder\EntityOrderInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;

class OrderBuilder 
{
    const ORDER_DEFS_METHOD_NAME = 'getOrderDefs';

    public function applyOrder(QueryBuilder $qb, string $entityClass, OrderDTOInterface $orderDTO, array $defaultOrderMap, array $relationMap = []): void
    {
        $availableOrderDefs = method_exists($entityClass, self::ORDER_DEFS_METHOD_NAME) ? $entityClass::{self::ORDER_DEFS_METHOD_NAME}() : [];
        $availableOrderDefs = array_merge($availableOrderDefs, $this->getRelationOrderDefs($relationMap));

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

    private function getRelationOrderDefs(array $relationMap, string $parameterNamePrefix = ''): array
    {
        $availableOrderDefs = [];
        foreach($relationMap as $relation => $map){
            if(!method_exists($map['class'], self::ORDER_DEFS_METHOD_NAME)){
                continue;
            }

            $relationSnakeCase = (new CamelCaseToSnakeCaseNameConverter)->normalize($relation);
            $classOrderDefs = $map['class']::{self::ORDER_DEFS_METHOD_NAME}();
            $relationParameterNamePrefix = "{$parameterNamePrefix}$relationSnakeCase.";
            $relationOrderDefs = array_combine(
                array_map(fn($parameterName) => "{$relationParameterNamePrefix}$parameterName", array_keys($classOrderDefs)),
                array_map(fn($order) => $order->setQbIdentifier($map['qbIdentifier']), array_values($classOrderDefs)),
            );

            $subRelationOrderDefs = !empty($map['relationMap']) ? $this->getRelationOrderDefs($map['relationMap'], $relationParameterNamePrefix) : [];
            $availableOrderDefs = array_merge($availableOrderDefs, $relationOrderDefs, $subRelationOrderDefs);
        }

        return $availableOrderDefs;
    }
}