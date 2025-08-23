<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\TimeWindow;
use App\Repository\Trait\RepositoryUtils;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TimeWindow>
 *
 * @method TimeWindow|null find($id, $lockMode = null, $lockVersion = null)
 * @method TimeWindow|null findOneBy(array $criteria, array $orderBy = null)
 * @method TimeWindow[]    findAll()
 * @method TimeWindow[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TimeWindowRepository extends ServiceEntityRepository implements RepositoryUtilsInterface
{
    use RepositoryUtils;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TimeWindow::class);
    }

    public function save(TimeWindow $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(TimeWindow $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return TimeWindow[] Returns an array of TimeWindow objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('t.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?TimeWindow
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
