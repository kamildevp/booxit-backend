<?php

declare(strict_types=1);

namespace App\Repository\Filter\EntityFilter;

use App\DTO\LocationRadiusFilterDTO;
use Doctrine\ORM\QueryBuilder;

class LocationRadiusFilter extends AbstractFieldFilter
{
    public function supports(mixed $value): bool
    {
        return $value instanceof LocationRadiusFilterDTO;
    }

    public function apply(QueryBuilder $qb, mixed $value, string $filterId): void
    {
        if(!$value instanceof LocationRadiusFilterDTO){
            return;
        }

        $lat = $value->lat;
        $lng = $value->lng;
        $radiusM = $value->radius*1000;
        
        $qb->andWhere("EARTH_BOX_CONTAINS(EARTH_POINT(:lat, :lng), :radius, EARTH_POINT($this->qbIdentifier.latitude, $this->qbIdentifier.longitude)) = TRUE")
        ->andWhere("EARTH_DISTANCE(EARTH_POINT(:lat, :lng), EARTH_POINT($this->qbIdentifier.latitude, $this->qbIdentifier.longitude)) < :radius")
        ->setParameter('lat', $lat)
        ->setParameter('lng', $lng)
        ->setParameter('radius', $radiusM);
    }
}