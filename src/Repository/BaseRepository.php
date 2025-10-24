<?php

declare(strict_types=1);

namespace App\Repository;

use App\DTO\FiltersDTOInterface;
use App\DTO\ListQueryDTOInterface;
use App\DTO\OrderDTOInterface;
use App\Enum\OrderDir;
use App\Exceptions\EntityNotFoundException;
use App\Repository\Filter\FiltersBuilder;
use App\Repository\Order\OrderBuilder;
use App\Repository\Pagination\Model\PaginationResult;
use App\Repository\Pagination\PaginationBuilder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @template T of object
 * @template-extends ServiceEntityRepository<T>
 */
abstract class BaseRepository extends ServiceEntityRepository
{
    const DEFAULT_ENTRIES_PER_PAGE = 20; 
    const MAX_ENTRIES_PER_PAGE = 100;
    const DEFAULT_ORDER_MAP = ['id' => OrderDir::ASC->value];
    const QB_IDENTIFIER = 'e';

    protected FiltersBuilder $filtersBuilder;
    protected OrderBuilder $orderBuilder;

    public function __construct(ManagerRegistry $registry, FiltersBuilder $filtersBuilder, OrderBuilder $orderBuilder, string $entityClass)
    {
        $this->filtersBuilder = $filtersBuilder;
        $this->orderBuilder = $orderBuilder;
        parent::__construct($registry, $entityClass);
    }

    /**
     * @return T
     * @throws EntityNotFoundException
     */
    public function findOrFail($id, $lockMode = null, $lockVersion = null)
    {
        $entity = $this->find($id, $lockMode, $lockVersion);
        if(empty($entity)){
            throw new EntityNotFoundException($this->getEntityName());
        }
        
        return $entity;
    }

    public function paginate(ListQueryDTOInterface $queryDTO, array $joinRelations = [], ?QueryBuilder $qb = null): PaginationResult
    {
        $qb = $qb ?? $this->createQueryBuilder(static::QB_IDENTIFIER);
        $relationMap = $this->joinRelations($qb, $joinRelations);

        if(isset($queryDTO->filters) && $queryDTO->filters instanceof FiltersDTOInterface){
            $this->applyFilters($qb, $this->getEntityName(), $queryDTO->filters, static::QB_IDENTIFIER, $relationMap);
        }

        $this->applyOrder($qb, $queryDTO, $relationMap);
        
        $paginationBuilder = new PaginationBuilder(static::MAX_ENTRIES_PER_PAGE);
        return $paginationBuilder->paginate($qb, $queryDTO);
    }

    protected function joinRelations(QueryBuilder $qb, array $relations = [], string $qbIdentifier = self::QB_IDENTIFIER): array
    {
        $relationMap = [];
        $relationIndx = 0;
        foreach($relations as $relation => $joinDef){
            $relationQbIdentifier = "{$qbIdentifier}r$relationIndx";
            $qb->leftJoin("$qbIdentifier.$relation", $relationQbIdentifier)
                ->addSelect($relationQbIdentifier);

            if(is_array($joinDef)){
                [$entityClass, $subRelations] = $joinDef;
                $subRelationMap = $this->joinRelations($qb, $subRelations, $relationQbIdentifier);
            }
            else{
                $entityClass = $joinDef;
            }

            $relationMap[$relation] = ['qbIdentifier' => $relationQbIdentifier, 'class' => $entityClass, 'relationMap' => $subRelationMap ?? []];
            $relationIndx++;
        }
        return $relationMap;
    }

    private function applyFilters(QueryBuilder $qb, string $entityClass, FiltersDTOInterface $filtersDTO, string $qbIdentifier = self::QB_IDENTIFIER, array $relatedEntityMap = []): void
    {
        $this->filtersBuilder->applyFilters($qb, $entityClass, $filtersDTO, $qbIdentifier);
        $this->applyRelatedFilters($this->filtersBuilder, $qb, $relatedEntityMap, $filtersDTO);
    }

    private function applyRelatedFilters(FiltersBuilder $filtersBuilder, QueryBuilder $qb, array $relationMap, FiltersDTOInterface $filtersDTO): void
    {
        foreach($relationMap as $relation => $map){
            if(isset($filtersDTO->{$relation}) && $filtersDTO->{$relation} instanceof FiltersDTOInterface){
                $filtersBuilder->applyFilters($qb, $map['class'], $filtersDTO->{$relation}, $map['qbIdentifier']);
                if(!empty($map['relationMap'])){
                    $this->applyRelatedFilters($filtersBuilder, $qb, $map['relationMap'], $filtersDTO->{$relation});
                }
            }
        }
    }

    private function applyOrder(QueryBuilder $qb, OrderDTOInterface $orderDTO, array $relationMap): void
    {
        $this->orderBuilder->applyOrder($qb, $this->getEntityName(), $orderDTO, static::DEFAULT_ORDER_MAP, $relationMap);
    }

    public function findOneByFieldValue(string $fieldName, mixed $value, array $excludeBy = [])
    {
        $qb = $this->createQueryBuilder(static::QB_IDENTIFIER);
        $qb->where(static::QB_IDENTIFIER.".$fieldName = :value")->setParameter('value', $value);

        $loopIndx = 0;
        foreach($excludeBy as $column => $columnValue){
            $qb->andWhere(static::QB_IDENTIFIER.".$column NOT IN (:exval$loopIndx)")->setParameter("exval$loopIndx", $columnValue);
            $loopIndx++;
        }

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function paginateRelatedTo(ListQueryDTOInterface $queryDTO, array $relatedTo, array $joinRelations = []): PaginationResult
    {
        $qb = $this->createQueryBuilder(static::QB_IDENTIFIER);
        $relationIndx = 0;
        foreach($relatedTo as $relation => $relatedEntity){
            $isCollection = $this->getEntityManager()->getClassMetadata($this->getEntityName())->isCollectionValuedAssociation($relation);
            if($isCollection){
                $qbIdentifier = "cr$relationIndx";
                $qbParameter = "crp$relationIndx";
                $qb->innerJoin(static::QB_IDENTIFIER.".$relation", $qbIdentifier)
                    ->andWhere("$qbIdentifier = :$qbParameter")
                    ->setParameter($qbParameter, $relatedEntity);
                $relationIndx++;
            }
            else{
                $qb->andWhere(static::QB_IDENTIFIER.".$relation = :relatedTo")->setParameter('relatedTo', $relatedEntity);
            }
        }

        return $this->paginate($queryDTO, $joinRelations, $qb);
    }
    
    public function flush(): void
    {
        $this->getEntityManager()->flush();
    }

}
