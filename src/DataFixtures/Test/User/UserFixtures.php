<?php

declare(strict_types=1);

namespace App\DataFixtures\Test\User;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $hasher)
    {
        
    }

    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i <= 35; $i++) {
            $user = new User();
            $user->setName('Test User ' . $i);
            $user->setEmail("user{$i}@example.com");
            $user->setPassword(
                $this->hasher->hashPassword($user, 'password123')
            );
            $user->setVerified((bool) random_int(0, 1));

            $manager->persist($user);
        }

        $manager->flush();
    }
}
