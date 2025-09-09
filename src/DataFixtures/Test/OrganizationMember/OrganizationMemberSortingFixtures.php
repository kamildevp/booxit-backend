<?php

declare(strict_types=1);

namespace App\DataFixtures\Test\OrganizationMember;

use App\DataFixtures\Test\User\UserSortingFixtures;
use App\Entity\Organization;
use App\Entity\OrganizationMember;
use App\Entity\User;
use App\Tests\Feature\Global\DataProvider\ListDataProvider;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class OrganizationMemberSortingFixtures extends Fixture
{    
    public function load(ObjectManager $manager): void
    {
        $sortedData = ListDataProvider::getSortedColumnsValuesSequence([
            'role' => 'organization_role',
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
            $organizationMember = new OrganizationMember();
            $organizationMember->setOrganization($organization);
            $organizationMember->setAppUser($this->getReference(UserSortingFixtures::USER_REFERENCE.$i, User::class));
            $organizationMember->setRole($item['role']);
            $manager->persist($organizationMember);
        }

        $manager->flush();
    }
}
