<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\EmailConfirmation;
use App\Entity\Reservation;
use App\Entity\Schedule;
use App\Enum\Reservation\ReservationStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Repository\Filter\FiltersBuilder;
use App\Repository\Order\OrderBuilder;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;

/**
 * @extends ServiceEntityRepository<Reservation>
 *
 * @method Reservation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Reservation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Reservation[]    findAll()
 * @method Reservation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReservationRepository extends BaseRepository
{
    public function __construct(ManagerRegistry $registry, FiltersBuilder $filtersBuilder, OrderBuilder $orderBuilder)
    {
        parent::__construct($registry, $filtersBuilder, $orderBuilder, Reservation::class);
    }

    public function save(Reservation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Reservation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Reservation[]
    */
    public function getScheduleReservations(
        Schedule $schedule, 
        DateTimeInterface|string|null $dateFrom, 
        DateTimeInterface|string|null $dateTo,
        array $joinRelations = []
    )
    {
        $defaultStartDate = (new DateTimeImmutable())->modify('monday this week')->setTime(0, 0);
        $defaultEndDate = (new DateTimeImmutable())->modify('sunday this week')->setTime(23, 59);

        $startDate = is_string($dateFrom) ? DateTimeImmutable::createFromFormat('Y-m-d', $dateFrom)->setTime(0, 0) : $dateFrom;
        $endDate = is_string($dateTo) ? DateTimeImmutable::createFromFormat('Y-m-d', $dateTo)->setTime(23, 59) : $dateTo;

        $startDate = $startDate instanceof DateTimeInterface ? $startDate : $defaultStartDate;
        $endDate = $dateTo instanceof DateTimeInterface ? $endDate : $defaultEndDate;

        $qb = $this->createQueryBuilder('e')
            ->where('e.schedule = :schedule')
            ->andWhere('e.startDateTime >= :startDate')
            ->andWhere('e.endDateTime <= :endDate')
            ->andWhere('e.status NOT IN (:excludedStatuses)')
            ->setParameter('schedule', $schedule)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->setParameter('excludedStatuses', [
                ReservationStatus::CUSTOMER_CANCELLED->value, 
                ReservationStatus::ORGANIZATION_CANCELLED->value
            ])
            ->orderBy('e.startDateTime', 'asc');

        $this->joinRelations($qb, $joinRelations, 'e');

        return $qb->getQuery()->getResult();
    }

    public function hardDelete(Reservation $reservation): void
    {
        $this->getEntityManager()->getFilters()->disable('softdeleteable');
        $this->createQueryBuilder('r')
            ->delete()
            ->where('r.id = :id')
            ->setParameter('id', $reservation->getId())
            ->getQuery()
            ->execute();
        $this->getEntityManager()->getFilters()->enable('softdeleteable');
    }

    public function findEmailConfirmationReservation(EmailConfirmation $emailConfirmation): ?Reservation
    {
        $qb = $this->createQueryBuilder('e')
            ->innerJoin("e.emailConfirmations", 'ec')
            ->andWhere("ec = :ec")
            ->setParameter('ec', $emailConfirmation);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function removeExpiredReservations(): void
    {
        $now = new DateTime();
        $qb = $this->createQueryBuilder('r')
            ->delete()
            ->where('r.expiryDate IS NOT NULL')
            ->andWhere('r.expiryDate < :now')
            ->andWhere('r.verified = :verified')
            ->setParameter('now', $now)
            ->setParameter('verified', false);

        $qb->getQuery()->execute();
    }
}
