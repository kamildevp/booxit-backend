<?php

declare(strict_types=1);

namespace App\DataFixtures\Test\User;

use App\Entity\User;
use App\Tests\Utils\DataProvider\ListDataProvider;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserSortingFixtures extends Fixture
{    
    const USER_REFERENCE = 'user-sort';

    public function __construct(private UserPasswordHasherInterface $hasher)
    {

    }

    public function load(ObjectManager $manager): void
    {
        $sortedData = ListDataProvider::getSortedColumnsValuesSequence([
            'name' => 'string',
            'email' => 'email',
            'verified' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ]);

        $data = [
            $sortedData[2],
            $sortedData[0],
            $sortedData[1],
        ];

        foreach($data as $i => $item){
            $user = new User();
            $user->setName($item['name']);
            $user->setEmail($item['email']);
            $user->setPassword(
                $this->hasher->hashPassword($user, 'password123')
            );
            $user->setVerified($item['verified']);
            $user->setCreatedAt(new DateTimeImmutable($item['created_at']));
            $user->setUpdatedAt(new DateTimeImmutable($item['updated_at']));

            $manager->persist($user);
            $this->addReference(self::USER_REFERENCE.$i, $user);
        }

        $manager->flush();
    }
}
