<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\WeekdayTimeWindow;
use App\Repository\Filter\FiltersBuilder;
use App\Repository\Order\OrderBuilder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<WeekdayTimeWindow>
 */
class WeekdayTimeWindowRepository extends BaseRepository
{
    public function __construct(ManagerRegistry $registry, FiltersBuilder $filtersBuilder, OrderBuilder $orderBuilder)
    {
        parent::__construct($registry, $filtersBuilder, $orderBuilder, WeekdayTimeWindow::class);
    }
}
