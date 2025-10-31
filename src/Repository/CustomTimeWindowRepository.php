<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\CustomTimeWindow;
use App\Entity\Schedule;
use App\Repository\Filter\FiltersBuilder;
use App\Repository\Order\OrderBuilder;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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
    public function getScheduleCustomTimeWindows(Schedule $schedule, DateTimeInterface $startDate, DateTimeInterface $endDate)
    {
        $qb = $this->createQueryBuilder('e')
            ->where('e.schedule = :schedule')
            ->andWhere('e.date >= :startDate')
            ->andWhere('e.date <= :endDate')
            ->setParameter('schedule', $schedule)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('e.date', 'asc')
            ->addOrderBy('e.startTime', 'asc');

        return $qb->getQuery()->getResult();
    }

    public function removeScheduleCustomTimeWindowsForDate(Schedule $schedule, DateTimeInterface|string $date): void
    {
        $date = is_string($date) ? DateTimeImmutable::createFromFormat('Y-m-d', $date) : $date;
        $qb = $this->createQueryBuilder('e')
            ->delete()
            ->where('e.schedule = :schedule')
            ->andWhere('e.date = :date')
            ->setParameter('schedule', $schedule)
            ->setParameter('date', $date);

        $qb->getQuery()->execute();
    }
}
