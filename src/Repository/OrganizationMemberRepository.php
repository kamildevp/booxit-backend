<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\OrganizationMember;
use App\Repository\Trait\RepositoryUtils;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<OrganizationMember>
 *
 * @method OrganizationMember|null find($id, $lockMode = null, $lockVersion = null)
 * @method OrganizationMember|null findOneBy(array $criteria, array $orderBy = null)
 * @method OrganizationMember[]    findAll()
 * @method OrganizationMember[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrganizationMemberRepository extends ServiceEntityRepository implements RepositoryUtilsInterface
{
    use RepositoryUtils;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OrganizationMember::class);
    }

    public function save(OrganizationMember $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(OrganizationMember $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return OrganizationMember[] Returns an array of OrganizationMember objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('o')
//            ->andWhere('o.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('o.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?OrganizationMember
//    {
//        return $this->createQueryBuilder('o')
//            ->andWhere('o.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
