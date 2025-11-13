<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo\User;

use App\DataFixtures\Demo\User\DataProvider\UserDataProvider;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class UserFixtures extends Fixture implements FixtureGroupInterface
{    
    public function load(ObjectManager $manager): void
    {
        $data = UserDataProvider::getData();

        foreach($data as $i => $item){
            $user = new User();
            $user->setName($item['name']);
            $user->setEmail($item['email']);
            $user->setUsername($item['username']);
            $user->setPassword('password123');
            $user->setVerified(true);
            $user->setCreatedAt(new DateTimeImmutable($item['created_at']));
            $user->setUpdatedAt(new DateTimeImmutable($item['updated_at']));

            $manager->persist($user);
            $this->addReference(('user').($i+1), $user);
        }

        $manager->flush();
    }

    public static function getGroups(): array
    {
        return [
            'demo'
        ];
    }
}
