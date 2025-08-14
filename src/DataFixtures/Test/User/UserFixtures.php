<?php

namespace App\DataFixtures\Test\User;

use App\DataFixtures\Trait\FieldValueFormatter;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    use FieldValueFormatter;

    protected array $fieldValues = [
        3 => [
            'name' => 'Sort C User'
        ],
        5 => [
            'name' => 'Sort A User'
        ],
        9 => [
            'name' => 'Sort B User'
        ],
    ];
    
    
    public function __construct(private UserPasswordHasherInterface $hasher)
    {
        
    }

    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i <= 35; $i++) {
            $user = new User();
            $user->setName($this->getFieldValue($i, 'name', 'Test User ' . $i));
            $user->setEmail($this->getFieldValue($i, 'email', "user{$i}@example.com"));
            $user->setPassword(
                $this->hasher->hashPassword($user, $this->getFieldValue($i, 'password', 'password123'))
            );
            $user->setVerified($this->getFieldValue($i, 'verified', (bool) random_int(0, 1)));

            $manager->persist($user);
        }

        $manager->flush();
    }
}
