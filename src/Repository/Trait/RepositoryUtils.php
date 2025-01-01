<?php

namespace App\Repository\Trait;

use App\Exceptions\EntityNotFoundException;
use App\Repository\Model\PaginationResult;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;

trait RepositoryUtils
{
    const ENTRIES_PER_PAGE = 20; 

    public function findOrFail($id, $lockMode = null, $lockVersion = null): object
    {
        $entity = $this->find($id, $lockMode, $lockVersion);
        if(empty($entity)){
            throw new EntityNotFoundException($this->getEntityName());
        }
        
        return $entity;
    }

    public function paginate(int $page, $perPage = self::ENTRIES_PER_PAGE, ?QueryBuilder $qb = null): PaginationResult
    {
        $offset = ($page - 1) * $perPage;
        $qb = ($qb ?? $this->createQueryBuilder('e'))
            ->setFirstResult($offset)
            ->setMaxResults($perPage);
        $paginator = new Paginator($qb);
        $total = count($paginator);
        
        $result = new PaginationResult();
        $result->setItems(iterator_to_array($paginator));
        $result->setPage($page);
        $result->setPerPage($perPage);
        $result->setPagesCount(ceil($total / $perPage));
        $result->setTotal($total);

        return $result;
    }
}
