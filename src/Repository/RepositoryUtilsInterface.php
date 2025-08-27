<?php

declare(strict_types=1);

namespace App\Repository;

use App\DTO\FiltersDTOInterface;
use App\DTO\OrderDTOInterface;
use App\DTO\PaginationDTO;
use App\Repository\Pagination\Model\PaginationResult;
use Doctrine\ORM\QueryBuilder;

interface RepositoryUtilsInterface
{
    public function findOrFail($id, $lockMode = null, $lockVersion = null): object;

    public function paginate(PaginationDTO $paginationDTO, ?FiltersDTOInterface $filtersDTO = null, ?OrderDTOInterface $orderDTO = null, ?QueryBuilder $qb = null): PaginationResult;

    public function findOneByFieldValue(string $fieldName, mixed $value, array $excludeBy = []);
}
