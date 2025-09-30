<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\OrganizationMember;
use App\Entity\User;
use App\Enum\Organization\OrganizationRole;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Repository\Filter\FiltersBuilder;
use App\Repository\Order\OrderBuilder;

/**
 * @extends ServiceEntityRepository<OrganizationMember>
 *
 * @method OrganizationMember|null find($id, $lockMode = null, $lockVersion = null)
 * @method OrganizationMember|null findOneBy(array $criteria, array $orderBy = null)
 * @method OrganizationMember[]    findAll()
 * @method OrganizationMember[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrganizationMemberRepository extends BaseRepository
{
    public function __construct(ManagerRegistry $registry, FiltersBuilder $filtersBuilder, OrderBuilder $orderBuilder)
    {
        parent::__construct($registry, $filtersBuilder, $orderBuilder, OrganizationMember::class);
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

    public function getOrganizationMembersCount(int $organizationId, ?OrganizationRole $role = null): int
    {
        $qb = $this->createQueryBuilder('om')
                    ->select('COUNT(om.id)')
                    ->where('om.organization = :organization')
                    ->setParameter('organization', $organizationId);

        if($role){
            $qb->andWhere('om.role = :role')->setParameter('role', $role->value);
        }
        
        return (int)$qb->getQuery()->getSingleScalarResult();
    }

    public function countOrganizationsWhereUserIsTheOnlyAdmin(User $user): int
    {
        $qb = $this->createQueryBuilder('om')
            ->select('COUNT(DISTINCT om.id)')
            ->andWhere('om.appUser = :user')
            ->andWhere('om.role = :adminRole')
            ->andWhere(
                'NOT EXISTS (
                    SELECT 1 FROM App\Entity\OrganizationMember om2
                    WHERE om2.organization = om.organization
                    AND om2.role = :adminRole
                    AND om2.appUser != :user
                )'
            )
            ->setParameter('user', $user)
            ->setParameter('adminRole', OrganizationRole::ADMIN->value);

        return (int) $qb->getQuery()->getSingleScalarResult();
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
