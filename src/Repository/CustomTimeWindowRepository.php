<?php

namespace App\Repository;

use App\Entity\CustomTimeWindow;
use App\Entity\Schedule;
use DateTimeImmutable;
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

    public function flush(): void
    {
        $this->getEntityManager()->flush();
    }

    public function getScheduleCustomTimeWindows(Schedule $schedule, ?string $dateFrom, ?string $dateTo)
    {
        $startDate = !is_null($dateFrom) ? DateTimeImmutable::createFromFormat('Y-m-d', $dateFrom) : (new \DateTimeImmutable())->modify('monday this week');
        $endDate = !is_null($dateTo) ? DateTimeImmutable::createFromFormat('Y-m-d', $dateTo) : (new \DateTimeImmutable())->modify('sunday this week');

        $qb = $this->createQueryBuilder('e')
            ->where('e.schedule = :schedule')
            ->andWhere('e.date >= :startDate')
            ->andWhere('e.date <= :endDate')
            ->setParameter('schedule', $schedule)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('e.date', 'asc');

        return $qb->getQuery()->getResult();
    }
}
