<?php

namespace App\DataFixtures\Test\User;

use App\Entity\EmailConfirmation;
use App\Entity\User;
use App\Enum\EmailConfirmationType;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class PasswordResetFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $hasher)
    {
        
    }

    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setName('Test User');
        $user->setEmail('user@example.com');
        $user->setPassword($this->hasher->hashPassword($user, 'password123'));
        $user->setVerified(true);
        $manager->persist($user);

        $emailConfirmation = new EmailConfirmation();
        $emailConfirmation->setCreator($user);
        $emailConfirmation->setEmail('user@example.com');
        $emailConfirmation->setExpiryDate(new DateTime('+1 day'));
        $emailConfirmation->setVerificationHandler('test');
        $emailConfirmation->setType(EmailConfirmationType::PASSWORD_RESET->value);
        $manager->persist($emailConfirmation);

        $manager->flush();
    }
}
