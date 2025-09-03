<?php

declare(strict_types=1);

namespace App\Repository\Filter;

use App\DTO\FiltersDTOInterface;
use App\Repository\Filter\EntityFilter\EntityFilterInterface;
use Doctrine\ORM\QueryBuilder;

class FiltersBuilder 
{
    const FILTER_DEFS_METHOD_NAME = 'getFilterDefs';

    public function applyFilters(QueryBuilder $qb, string $entityClass, FiltersDTOInterface $filtersDTO): void
    {
        if(!method_exists($entityClass, self::FILTER_DEFS_METHOD_NAME)){
            return;
        }

        $filterDefs = $entityClass::{self::FILTER_DEFS_METHOD_NAME}();

        $filterIndx = 0;
        foreach($filterDefs as $parameterName => $filter){  
            $filterValue = $filtersDTO->getFilter($parameterName);
            if(!$filter instanceof EntityFilterInterface || !$filtersDTO->hasFilter($parameterName) || !$filter->supports($filterValue)){
                continue;
            }

            $filter->apply($qb, $filterValue, "filterParam$filterIndx");
            $filterIndx++;
        }
    }
}