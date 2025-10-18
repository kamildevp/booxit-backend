<?php

namespace App\Repository;

use App\Entity\CustomTimeWindow;
use App\Entity\Schedule;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CustomTimeWindow>
 */
class CustomTimeWindowRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CustomTimeWindow::class);
    }

    //    /**
    //     * @return CustomTimeWindow[] Returns an array of CustomTimeWindow objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('d')
    //            ->andWhere('d.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('d.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?CustomTimeWindow
    //    {
    //        return $this->createQueryBuilder('d')
    //            ->andWhere('d.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

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
