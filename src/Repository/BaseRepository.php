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

abstract class BaseRepository extends ServiceEntityRepository implements RepositoryUtilsInterface
{
    const DEFAULT_ENTRIES_PER_PAGE = 20; 
    const MAX_ENTRIES_PER_PAGE = 100;
    const DEFAULT_ORDER_MAP = ['id' => OrderDir::ASC->value];
    const QB_IDENTIFIER = 'e';

    public function __construct(ManagerRegistry $registry, string $entityClass)
    {
        parent::__construct($registry, $entityClass);
    }

    public function findOrFail($id, $lockMode = null, $lockVersion = null): object
    {
        $entity = $this->find($id, $lockMode, $lockVersion);
        if(empty($entity)){
            throw new EntityNotFoundException($this->getEntityName());
        }
        
        return $entity;
    }

    public function paginate(ListQueryDTOInterface $queryDTO, array $joinRelations = [], ?QueryBuilder $qb = null): PaginationResult
    {
        $qb = $qb ?? $this->createQueryBuilder(self::QB_IDENTIFIER);
        $relationMap = $this->joinRelations($qb, $joinRelations);

        if(isset($queryDTO->filters) && $queryDTO->filters instanceof FiltersDTOInterface){
            $this->applyFilters($qb, $this->getEntityName(), $queryDTO->filters, self::QB_IDENTIFIER, $relationMap);
        }

        $this->applyOrder($qb, $queryDTO, $relationMap);
        
        $paginationBuilder = new PaginationBuilder(self::MAX_ENTRIES_PER_PAGE);
        return $paginationBuilder->paginate($qb, $queryDTO);
    }

    protected function joinRelations(QueryBuilder $qb, array $relations = []): array
    {
        $relationMap = [];
        $relationIndx = 0;
        foreach($relations as $relation => $entityClass){
            $qbIdentifier = "r$relationIndx";
            $qb->leftJoin(self::QB_IDENTIFIER.".$relation", $qbIdentifier)
                ->addSelect($qbIdentifier);

            $relationMap[$relation] = ['qbIdentifier' => $qbIdentifier, 'class' => $entityClass];
            $relationIndx++;
        }
        return $relationMap;
    }

    private function applyFilters(QueryBuilder $qb, string $entityClass, FiltersDTOInterface $filtersDTO, string $qbIdentifier = self::QB_IDENTIFIER, array $relatedEntityMap = []): void
    {
        $filtersBuilder = new FiltersBuilder();
        $filtersBuilder->applyFilters($qb, $entityClass, $filtersDTO, $qbIdentifier);
        $this->applyRelatedFilters($filtersBuilder, $qb, $relatedEntityMap, $filtersDTO);
    }

    private function applyRelatedFilters(FiltersBuilder $filtersBuilder, QueryBuilder $qb, array $relationMap, FiltersDTOInterface $filtersDTO): void
    {
        foreach($relationMap as $relation => $map){
            if(isset($filtersDTO->{$relation}) && $filtersDTO->{$relation} instanceof FiltersDTOInterface){
                $filtersBuilder->applyFilters($qb, $map['class'], $filtersDTO->{$relation}, $map['qbIdentifier']);
            }
        }
    }

    private function applyOrder(QueryBuilder $qb, OrderDTOInterface $orderDTO, array $relationMap): void
    {
        $orderBuilder = new OrderBuilder();
        $orderBuilder->applyOrder($qb, $this->getEntityName(), $orderDTO, self::DEFAULT_ORDER_MAP, $relationMap);
    }

    public function findOneByFieldValue(string $fieldName, mixed $value, array $excludeBy = [])
    {
        $qb = $this->createQueryBuilder(self::QB_IDENTIFIER);
        $qb->where(self::QB_IDENTIFIER.".$fieldName = :value")->setParameter('value', $value);

        $loopIndx = 0;
        foreach($excludeBy as $column => $columnValue){
            $qb->andWhere(self::QB_IDENTIFIER.".$column NOT IN (:exval$loopIndx)")->setParameter("exval$loopIndx", $columnValue);
            $loopIndx++;
        }

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function paginateRelatedTo(ListQueryDTOInterface $queryDTO, array $relatedTo, array $joinRelations = []): PaginationResult
    {
        $qb = $this->createQueryBuilder(self::QB_IDENTIFIER);
        $relationIndx = 0;
        foreach($relatedTo as $relation => $relatedEntity){
            $isCollection = $this->getEntityManager()->getClassMetadata($this->getEntityName())->isCollectionValuedAssociation($relation);
            if($isCollection){
                $qbIdentifier = "cr$relationIndx";
                $qbParameter = "crp$relationIndx";
                $qb->innerJoin(self::QB_IDENTIFIER.".$relation", $qbIdentifier)
                    ->andWhere("$qbIdentifier = :$qbParameter")
                    ->setParameter($qbParameter, $relatedEntity);
                $relationIndx++;
            }
            else{
                $qb->andWhere(self::QB_IDENTIFIER.".$relation = :relatedTo")->setParameter('relatedTo', $relatedEntity);
            }
        }

        return $this->paginate($queryDTO, $joinRelations, $qb);
    }

}
