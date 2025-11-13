<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo\OrganizationMember;

use App\DataFixtures\Demo\Organization\OrganizationFixtures;
use App\DataFixtures\Demo\OrganizationMember\DataProvider\OrganizationMemberDataProvider;
use App\DataFixtures\Demo\User\UserFixtures;
use App\Entity\Organization;
use App\Entity\OrganizationMember;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class OrganizationMemberFixtures extends Fixture implements FixtureGroupInterface, DependentFixtureInterface
{    
    public function load(ObjectManager $manager): void
    {
        $data = OrganizationMemberDataProvider::getData();

        foreach($data as $i => $item){
            $organization = $this->getReference($item['organization_reference'], Organization::class);
            $user = $this->getReference($item['user_reference'], User::class);
            $organizationMember = new OrganizationMember();
            $organizationMember->setOrganization($organization);
            $organizationMember->setAppUser($user);
            $organizationMember->setRole($item['role']);
            $manager->persist($organizationMember);

            $this->addReference(('organization_member').($i+1), $organizationMember);
        }

        $manager->flush();
    }

    public static function getGroups(): array
    {
        return [
            'demo'
        ];
    }

    public function getDependencies(): array
    {
        return [
            OrganizationFixtures::class,
            UserFixtures::class,
        ];
    }
}
