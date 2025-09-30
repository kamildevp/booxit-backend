<?php

declare(strict_types=1);

namespace App\Repository\Filter;

use App\DTO\FiltersDTOInterface;
use App\Repository\Filter\EntityFilter\EntityFilterInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class FiltersBuilder 
{
    const FILTER_DEFS_METHOD_NAME = 'getFilterDefs';

    public function __construct(private DenormalizerInterface&NormalizerInterface $normalizer)
    {
        
    }

    public function applyFilters(QueryBuilder $qb, string $entityClass, FiltersDTOInterface $filtersDTO, $qbIdentifier = 'e'): void
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

            $filter->setQbIdentifier($qbIdentifier);
            $filter->setNormalizer($this->normalizer);
            $filter->apply($qb, $filterValue, "filterParam$filterIndx");
            $filterIndx++;
        }
    }
}