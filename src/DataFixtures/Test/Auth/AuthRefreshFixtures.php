<?php

namespace App\DataFixtures\Test\Auth;

use App\DataFixtures\Test\Global\VerifiedUserFixtures;
use App\Entity\User;
use App\Service\Auth\AuthServiceInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AuthRefreshFixtures extends Fixture
{
    public function __construct(private AuthServiceInterface $authService)
    {
        
    }

    public function load(ObjectManager $manager): void
    {
        $this->authService->createUserRefreshToken($this->getReference(VerifiedUserFixtures::VERIFIED_USER_REFERENCE, User::class));
    }
}
