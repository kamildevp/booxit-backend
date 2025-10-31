<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ScheduleAssignment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Repository\Filter\FiltersBuilder;
use App\Repository\Order\OrderBuilder;

/**
 * @extends BaseRepository<ScheduleAssignment>
 *
 * @method ScheduleAssignment|null find($id, $lockMode = null, $lockVersion = null)
 * @method ScheduleAssignment|null findOneBy(array $criteria, array $orderBy = null)
 * @method ScheduleAssignment[]    findAll()
 * @method ScheduleAssignment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ScheduleAssignmentRepository extends BaseRepository
{
    public function __construct(ManagerRegistry $registry, FiltersBuilder $filtersBuilder, OrderBuilder $orderBuilder)
    {
        parent::__construct($registry, $filtersBuilder, $orderBuilder, ScheduleAssignment::class);
    }

    public function save(ScheduleAssignment $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ScheduleAssignment $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
