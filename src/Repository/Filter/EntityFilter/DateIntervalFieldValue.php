<?php

declare(strict_types=1);

namespace App\Repository\Filter\EntityFilter;

use App\Validator\Constraints\Compound\DateTimeStringRequirements;
use DateInterval;
use DateTimeImmutable;
use Doctrine\ORM\QueryBuilder;
use Exception;

class DateIntervalFieldValue extends AbstractFieldFilter
{
    public function __construct(string $propertyName, protected string $operator)
    {
        parent::__construct($propertyName);
    }

    public function supports(mixed $value): bool
    {
        if(!is_string($value)){
            return false;
        }
        
        try{
            new DateInterval($value);
        }
        catch(Exception){
            return false;
        }

        return true;
    }

    public function apply(QueryBuilder $qb, mixed $value, string $filterId): void
    {
        $di = new DateInterval($value);
        $qb->andWhere("$this->qbIdentifier.$this->propertyName $this->operator :$filterId")->setParameter($filterId, $di);
    }
}