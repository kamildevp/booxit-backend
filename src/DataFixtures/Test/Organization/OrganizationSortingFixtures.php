<?php

declare(strict_types=1);

namespace App\DataFixtures\Test\Organization;

use App\DataFixtures\Test\Global\VerifiedUserFixtures;
use App\Entity\Organization;
use App\Entity\OrganizationMember;
use App\Entity\Service;
use App\Entity\User;
use App\Tests\Utils\DataProvider\ListDataProvider;
use DateInterval;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class OrganizationSortingFixtures extends Fixture
{    
    public function load(ObjectManager $manager): void
    {
        $sortedData = ListDataProvider::getSortedColumnsValuesSequence([
            'name' => 'string',
            'service_category' => 'service_category',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'role' => 'organization_role'
        ]);

        $data = [
            $sortedData[2],
            $sortedData[0],
            $sortedData[1],
        ];

        foreach($data as $i => $item){
            $organization = new Organization();
            $organization->setName($item['name']);
            $organization->setCreatedAt(new DateTimeImmutable($item['created_at']));
            $organization->setUpdatedAt(new DateTimeImmutable($item['updated_at']));
            $manager->persist($organization);

            $service = new Service();
            $service->setOrganization($organization);
            $service->setName('Org Test Service ' . $i);
            $service->setCategory($item['service_category']);
            $service->setDescription('test');
            $service->setDuration(new DateInterval('PT30M'));
            $service->setEstimatedPrice('16.70');
            $manager->persist($service);

            $organizationMember = new OrganizationMember();
            $organizationMember->setOrganization($organization);
            $organizationMember->setAppUser($this->getReference(VerifiedUserFixtures::VERIFIED_USER_REFERENCE, User::class));
            $organizationMember->setRole($item['role']);
            $manager->persist($organizationMember);
        }

        $manager->flush();
    }
}
