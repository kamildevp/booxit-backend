<?php

namespace App\Repository;

use App\Entity\User;
use App\Repository\Trait\RepositoryUtils;
use Gesdinet\JWTRefreshTokenBundle\Entity\RefreshTokenRepository as BaseRefreshTokenRepository;

class RefreshTokenRepository extends BaseRefreshTokenRepository
{
    use RepositoryUtils;

    public function removeUserRefreshTokens(User $user): void
    {
        $qb = $this->createQueryBuilder('rt')
            ->delete()
            ->where('rt.username = :username')
            ->setParameter('username', $user->getEmail());

        $qb->getQuery()->execute();
    }
}
