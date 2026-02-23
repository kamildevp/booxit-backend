<?php

declare(strict_types=1);

namespace App\DataFixtures\Test\User;

use App\Entity\EmailConfirmation;
use App\Entity\User;
use App\Enum\EmailConfirmation\EmailConfirmationStatus;
use App\Enum\EmailConfirmation\EmailConfirmationType;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class VerifyUserEmailFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $hasher)
    {
        
    }

    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setName('Test User');
        $user->setEmail('olduser@example.com');
        $user->setPassword($this->hasher->hashPassword($user, 'password123'));
        $user->setVerified(false);
        $manager->persist($user);

        $emailConfirmation = new EmailConfirmation();
        $emailConfirmation->setCreator($user);
        $emailConfirmation->setEmail('newuser@example.com');
        $emailConfirmation->setExpiryDate(new DateTime('+1 day'));
        $emailConfirmation->setVerificationHandler('test');
        $emailConfirmation->setType(EmailConfirmationType::EMAIL_VERIFICATION->value);
        $emailConfirmation->setStatus(EmailConfirmationStatus::PENDING->value);
        $manager->persist($emailConfirmation);

        $manager->flush();
    }
}
