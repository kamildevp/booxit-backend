<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\EmailConfirmation;
use App\Entity\User;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EmailConfirmation>
 *
 * @method EmailConfirmation|null find($id, $lockMode = null, $lockVersion = null)
 * @method EmailConfirmation|null findOneBy(array $criteria, array $orderBy = null)
 * @method EmailConfirmation[]    findAll()
 * @method EmailConfirmation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EmailConfirmationRepository extends BaseRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EmailConfirmation::class);
    }

    public function save(EmailConfirmation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(EmailConfirmation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findActiveUserEmailConfirmationByType(User $user, string $type): ?EmailConfirmation
   {
       return $this->createQueryBuilder('e')
           ->andWhere('e.creator = :user')
           ->andWhere('e.type = :type')
           ->andWhere('e.expiryDate > :expiry_date')
           ->setParameter('user', $user)
           ->setParameter('type', $type)
           ->setParameter('expiry_date', new DateTime())
           ->getQuery()
           ->getOneOrNullResult()
       ;
   }


//    /**
//     * @return EmailConfirmation[] Returns an array of EmailConfirmation objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('e.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?EmailConfirmation
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
