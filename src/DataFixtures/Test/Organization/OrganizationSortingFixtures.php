<?php

declare(strict_types=1);

namespace App\DataFixtures\Test\Organization;

use App\DataFixtures\Test\Global\VerifiedUserFixtures;
use App\Entity\Organization;
use App\Entity\OrganizationMember;
use App\Entity\User;
use App\Enum\Organization\OrganizationRole;
use App\Tests\Feature\Global\DataProvider\ListDataProvider;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class OrganizationSortingFixtures extends Fixture
{    
    public function load(ObjectManager $manager): void
    {
        $sortedData = ListDataProvider::getSortedColumnsValuesSequence([
            'name' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ]);

        $data = [
            $sortedData[2],
            $sortedData[0],
            $sortedData[1],
        ];

        foreach($data as $item){
            $organization = new Organization();
            $organization->setName($item['name']);
            $organization->setCreatedAt(new DateTimeImmutable($item['created_at']));
            $organization->setUpdatedAt(new DateTimeImmutable($item['updated_at']));
            $manager->persist($organization);

            $organizationMember = new OrganizationMember();
            $organizationMember->setOrganization($organization);
            $organizationMember->setAppUser($this->getReference(VerifiedUserFixtures::VERIFIED_USER_REFERENCE, User::class));
            $organizationMember->setRole(OrganizationRole::ADMIN->value);
            $manager->persist($organizationMember);
        }

        $manager->flush();
    }
}
