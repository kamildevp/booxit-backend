<?php

declare(strict_types=1);

namespace App\Repository;

use App\DTO\ListQueryDTOInterface;
use App\Repository\Pagination\Model\PaginationResult;
use Doctrine\ORM\QueryBuilder;

interface RepositoryUtilsInterface
{
    public function findOrFail($id, $lockMode = null, $lockVersion = null): object;

    public function paginate(ListQueryDTOInterface $queryDTO, array $joinRelations = [], ?QueryBuilder $qb = null): PaginationResult;

    public function paginateRelatedTo(ListQueryDTOInterface $queryDTO, array $relatedTo, array $joinRelations = []): PaginationResult;

    public function findOneByFieldValue(string $fieldName, mixed $value, array $excludeBy = []);
}
