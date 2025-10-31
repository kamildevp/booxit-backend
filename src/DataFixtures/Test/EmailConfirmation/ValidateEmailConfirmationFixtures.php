<?php

declare(strict_types=1);

namespace App\DataFixtures\Test\EmailConfirmation;

use App\Entity\EmailConfirmation;
use App\Entity\User;
use App\Enum\EmailConfirmation\EmailConfirmationStatus;
use App\Enum\EmailConfirmation\EmailConfirmationType;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ValidateEmailConfirmationFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $hasher)
    {
        
    }

    public function load(ObjectManager $manager): void
    {
        foreach(EmailConfirmationType::values() as $i => $type){
            $emailConfirmation = new EmailConfirmation();
            $emailConfirmation->setEmail("ecv-user$i@example.com");
            $emailConfirmation->setExpiryDate(new DateTime('+1 day'));
            $emailConfirmation->setVerificationHandler('test');
            $emailConfirmation->setType($type);
            $emailConfirmation->setStatus(EmailConfirmationStatus::PENDING->value);
            $manager->persist($emailConfirmation);
        }

        $manager->flush();
    }
}
