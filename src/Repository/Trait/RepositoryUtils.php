<?php

declare(strict_types=1);

namespace App\Repository\Trait;

use App\DTO\FiltersDTOInterface;
use App\DTO\OrderDTOInterface;
use App\DTO\PaginationDTO;
use App\Exceptions\EntityNotFoundException;
use App\Repository\Filter\FiltersBuilder;
use App\Repository\Order\OrderBuilder;
use App\Repository\Pagination\Model\PaginationResult;
use App\Repository\Pagination\PaginationBuilder;
use Doctrine\ORM\QueryBuilder;

trait RepositoryUtils
{
    const DEFAULT_ENTRIES_PER_PAGE = 20; 
    const MAX_ENTRIES_PER_PAGE = 100;
    const DEFAULT_ORDER_MAP = ['id' => 'asc'];

    public function findOrFail($id, $lockMode = null, $lockVersion = null): object
    {
        $entity = $this->find($id, $lockMode, $lockVersion);
        if(empty($entity)){
            throw new EntityNotFoundException($this->getEntityName());
        }
        
        return $entity;
    }

    public function paginate(PaginationDTO $paginationDTO, ?FiltersDTOInterface $filtersDTO = null, ?OrderDTOInterface $orderDTO = null, ?QueryBuilder $qb = null): PaginationResult
    {
        $qb = ($qb ?? $this->createQueryBuilder('e'));
        
        if($filtersDTO != null){
            $this->applyFilters($qb, $filtersDTO);
        }

        if($orderDTO != null){
            $this->applyOrder($qb, $orderDTO);
        }
        
        $paginationBuilder = new PaginationBuilder(self::MAX_ENTRIES_PER_PAGE);
        return $paginationBuilder->paginate($qb, $paginationDTO);
    }

    private function applyFilters(QueryBuilder $qb, FiltersDTOInterface $filtersDTO): void
    {
        $filtersBuilder = new FiltersBuilder();
        $filtersBuilder->applyFilters($qb, $this->getEntityName(), $filtersDTO);
    }

    private function applyOrder(QueryBuilder $qb, OrderDTOInterface $orderDTO): void
    {
        $orderBuilder = new OrderBuilder();
        $orderBuilder->applyOrder($qb, $this->getEntityName(), $orderDTO, self::DEFAULT_ORDER_MAP);
    }

    public function findOneByFieldValue(string $fieldName, mixed $value, array $excludeBy = [])
    {
        $qb = $this->createQueryBuilder('e');
        $qb->where("e.$fieldName = :value")->setParameter('value', $value);

        $loopIndx = 0;
        foreach($excludeBy as $column => $columnValue){
            $qb->andWhere("e.$column NOT IN (:exval$loopIndx)")->setParameter("exval$loopIndx", $columnValue);
            $loopIndx++;
        }

        return $qb->getQuery()->getOneOrNullResult();
    }
}
