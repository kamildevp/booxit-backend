<?php

declare(strict_types=1);

namespace App\DataFixtures\Test\User;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UnverifiedUserFixtures extends Fixture
{
    const USER_REFERENCE = 'unverified_user';

    public function __construct(private UserPasswordHasherInterface $hasher)
    {
        
    }

    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setName('Unverified Test User');
        $user->setEmail("unverified_user@example.com");
        $user->setUsername("unverified_user");
        $user->setPassword(
            $this->hasher->hashPassword($user, 'password123')
        );
        $user->setVerified(false);

        $manager->persist($user);
        $this->addReference(self::USER_REFERENCE, $user);

        $manager->flush();
    }
}
