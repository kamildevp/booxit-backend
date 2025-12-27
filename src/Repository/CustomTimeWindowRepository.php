<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\CustomTimeWindow;
use App\Entity\Schedule;
use App\Repository\Filter\FiltersBuilder;
use App\Repository\Order\OrderBuilder;
use DateTimeInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends BaseRepository<CustomTimeWindow>
 */
class CustomTimeWindowRepository extends BaseRepository
{
    public function __construct(ManagerRegistry $registry, FiltersBuilder $filtersBuilder, OrderBuilder $orderBuilder)
    {
        parent::__construct($registry, $filtersBuilder, $orderBuilder, CustomTimeWindow::class);
    }

    public function save(CustomTimeWindow $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(CustomTimeWindow $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return CustomTimeWindow[]
     */
    public function getScheduleCustomTimeWindows(Schedule $schedule, DateTimeInterface $startDateTime, DateTimeInterface $endDateTime)
    {
        $qb = $this->createQueryBuilder('e')
            ->where('e.schedule = :schedule')
            ->andWhere('e.startDateTime >= :startDateTime')
            ->andWhere('e.startDateTime <= :endDateTime')
            ->setParameter('schedule', $schedule)
            ->setParameter('startDateTime', $startDateTime)
            ->setParameter('endDateTime', $endDateTime)
            ->orderBy('e.startDateTime', 'asc');

        return $qb->getQuery()->getResult();
    }
}
