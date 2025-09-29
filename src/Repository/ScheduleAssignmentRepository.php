<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ScheduleAssignment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ScheduleAssignment>
 *
 * @method ScheduleAssignment|null find($id, $lockMode = null, $lockVersion = null)
 * @method ScheduleAssignment|null findOneBy(array $criteria, array $orderBy = null)
 * @method ScheduleAssignment[]    findAll()
 * @method ScheduleAssignment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ScheduleAssignmentRepository extends BaseRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ScheduleAssignment::class);
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

//    /**
//     * @return ScheduleAssignment[] Returns an array of ScheduleAssignment objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?ScheduleAssignment
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
