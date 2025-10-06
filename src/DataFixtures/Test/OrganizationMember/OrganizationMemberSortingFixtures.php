<?php

declare(strict_types=1);

namespace App\DataFixtures\Test\OrganizationMember;

use App\Entity\Organization;
use App\Entity\OrganizationMember;
use App\Entity\User;
use App\Tests\Utils\DataProvider\ListDataProvider;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class OrganizationMemberSortingFixtures extends Fixture
{    
    public function load(ObjectManager $manager): void
    {
        $sortedData = ListDataProvider::getSortedColumnsValuesSequence([
            'role' => 'organization_role',
            'app_user.name' => 'string'
        ]);

        $data = [
            $sortedData[2],
            $sortedData[0],
            $sortedData[1],
        ];

        $organization = new Organization();
        $organization->setName('Test Organization');
        $manager->persist($organization);

        foreach($data as $i => $item){
            $user = new User();
            $user->setName($item['app_user.name']);
            $user->setEmail("om-user{$i}@example.com");
            $user->setPassword('dummypass');
            $user->setVerified(true);
            $manager->persist($user);

            $organizationMember = new OrganizationMember();
            $organizationMember->setOrganization($organization);
            $organizationMember->setAppUser($user);
            $organizationMember->setRole($item['role']);
            $manager->persist($organizationMember);
        }

        $manager->flush();
    }
}
