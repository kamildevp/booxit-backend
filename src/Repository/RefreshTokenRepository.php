<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\RefreshToken;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Repository\Filter\FiltersBuilder;
use App\Repository\Order\OrderBuilder;
use DateTime;

/**
 * @extends BaseRepository<RefreshToken>
 *
 * @method RefreshToken|null find($id, $lockMode = null, $lockVersion = null)
 * @method RefreshToken|null findOneBy(array $criteria, array $orderBy = null)
 * @method RefreshToken[]    findAll()
 * @method RefreshToken[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RefreshTokenRepository extends BaseRepository
{
    public function __construct(ManagerRegistry $registry, FiltersBuilder $filtersBuilder, OrderBuilder $orderBuilder)
    {
        parent::__construct($registry, $filtersBuilder, $orderBuilder, RefreshToken::class);
    }

    public function save(RefreshToken $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(RefreshToken $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function removeAllUserRefreshTokens(User $user): void
    {
        $qb = $this->createQueryBuilder('rt')
            ->delete()
            ->where('rt.appUser = :user')
            ->setParameter('user', $user);

        $qb->getQuery()->execute();
    }

    public function removeAllUserRefreshTokensExceptIds(User $user, array $excludedIds): void
    {
        $qb = $this->createQueryBuilder('rt')
            ->delete()
            ->where('rt.appUser = :user')
            ->andWhere('rt.id NOT IN (:excluded_ids)')
            ->setParameter('user', $user)
            ->setParameter('excluded_ids', $excludedIds);

        $qb->getQuery()->execute();
    }

    public function removeExpiredRefreshTokens(): void
    {
        $now = new DateTime();
        $qb = $this->createQueryBuilder('rt')
            ->delete()
            ->where('rt.expiresAt < :now')
            ->setParameter('now', $now);

        $qb->getQuery()->execute();
    }
}
