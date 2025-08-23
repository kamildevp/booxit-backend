<?php

declare(strict_types=1);

namespace App\Repository\Filter;

use App\DTO\FiltersDTOInterface;
use App\Repository\Filter\EntityFilter\Attribute\Filter;
use App\Repository\Filter\EntityFilter\EntityFilterInterface;
use Doctrine\ORM\QueryBuilder;
use ReflectionClass;
use ReflectionProperty;

class FiltersBuilder {

    public function applyFilters(QueryBuilder $qb, string $entityClass, FiltersDTOInterface $filtersDTO): void
    {
        $entityReflection = new ReflectionClass($entityClass);

        $filterIndx = 0;
        foreach($entityReflection->getProperties() as $property){
            $filters = $this->getPropertyFilters($property);
            
            foreach($filters as $parameterName => $filter){
                $filterValue = $filtersDTO->getFilter($parameterName);
                if(!$filter instanceof EntityFilterInterface || !$filtersDTO->hasFilter($parameterName) || !$filter->supports($filterValue)){
                    continue;
                }
    
                $filter->apply($qb, $property->getName(), $filterValue, "filterParam$filterIndx");
                $filterIndx++;
            }
        }
    }

    private function getPropertyFilters(ReflectionProperty $property): array
    {        
        $filters = [];
        foreach($property->getAttributes(Filter::class) as $filterAttribute){
            $filterAttributeInstance = $filterAttribute->newInstance();
            $filters[$filterAttributeInstance->parameterName] = $filterAttributeInstance->entityFilter;
        }

        return $filters;
    }
}