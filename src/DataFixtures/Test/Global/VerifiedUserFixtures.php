<?php

declare(strict_types=1);

namespace App\DataFixtures\Test\Global;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class VerifiedUserFixtures extends Fixture
{
    const VERIFIED_USER_REFERENCE = 'verified-user';
    const VERIFIED_USER_EMAIL = 'verifieduser@example.com';
    const VERIFIED_USER_NAME = 'Test User';
    const VERIFIED_USER_PASSWORD = 'password123';

    public function __construct(private UserPasswordHasherInterface $hasher)
    {
        
    }

    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setName(self::VERIFIED_USER_NAME);
        $user->setEmail(self::VERIFIED_USER_EMAIL);
        $user->setPassword($this->hasher->hashPassword($user, self::VERIFIED_USER_PASSWORD));
        $user->setVerified(true);
        $manager->persist($user);

        $manager->flush();

        $this->addReference(self::VERIFIED_USER_REFERENCE, $user);
    }
}
