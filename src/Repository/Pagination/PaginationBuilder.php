<?php

declare(strict_types=1);

namespace App\Repository\Pagination;

use App\DTO\PaginationDTOInterface;
use App\Repository\Pagination\Model\PaginationResult;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;

class PaginationBuilder {

    public function __construct(
        private int $maxEntriesPerPage = 100
    )
    {
        
    }

    public function paginate(QueryBuilder $qb, PaginationDTOInterface $paginationDTO): PaginationResult
    {
        $offset = ($paginationDTO->getPage() - 1) * $paginationDTO->getPerPage();
        $perPage = min($paginationDTO->getPerPage(), $this->maxEntriesPerPage);    
        $qb->setFirstResult($offset)->setMaxResults($perPage);

        $paginator = new Paginator($qb);
        return $this->getPaginationResult($paginator, $paginationDTO->getPage(), $perPage);


    }

    private function getPaginationResult(Paginator $paginator, int $page, int $perPage): PaginationResult
    {
        $total = count($paginator);

        $result = new PaginationResult();
        $result->setItems(iterator_to_array($paginator));
        $result->setPage($page);
        $result->setPerPage($perPage);
        $result->setPagesCount((int)ceil($total / $perPage));
        $result->setTotal($total);

        return $result;
    }
}