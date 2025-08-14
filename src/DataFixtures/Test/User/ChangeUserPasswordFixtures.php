<?php

namespace App\DataFixtures\Test\User;

use App\DataFixtures\Test\Global\VerifiedUserFixtures;
use App\Entity\RefreshToken;
use App\Entity\User;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;

class ChangeUserPasswordFixtures extends Fixture
{
    public function __construct(private JWTEncoderInterface $jwtEncoder)
    {
        
    }

    public function load(ObjectManager $manager): void
    {
        $refreshToken = new RefreshToken();
        $user = $this->getReference(VerifiedUserFixtures::VERIFIED_USER_REFERENCE, User::class);
        $refreshToken->setAppUser($user);
        $manager->persist($refreshToken);
        $manager->flush();

        $expiryDate = new DateTime('+1 day');
        $value = $this->jwtEncoder->encode([
            'id' => $user->getId(),
            'roles' => $user->getRoles(),
            'refresh_token_id' => $refreshToken->getId(),
            'exp' => $expiryDate->getTimestamp(),
        ]);

        $refreshToken->setValue($value);
        $refreshToken->setExpiresAt($expiryDate);
        $manager->persist($refreshToken);
        $manager->flush();
    }
}
