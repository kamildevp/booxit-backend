<?php

declare(strict_types=1);

namespace App\DataFixtures\Test\Service;

use App\DataFixtures\Test\OrganizationMember\OrganizationAdminFixtures;
use App\Entity\Organization;
use App\Entity\Service;
use App\Tests\Utils\DataProvider\ListDataProvider;
use DateInterval;
use DateTime;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ServiceSortingFixtures extends Fixture implements DependentFixtureInterface
{    
    const SERVICE_REFERENCE = 'service-sort';

    public function load(ObjectManager $manager): void
    {
        $sortedData = ListDataProvider::getSortedColumnsValuesSequence([
            'name' => 'string',
            'duration' => 'dateinterval',
            'estimated_price' => 'decimal',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ]);

        $data = [
            $sortedData[2],
            $sortedData[0],
            $sortedData[1],
        ];

        foreach($data as $i => $item){
            $service = new Service();
            $service->setOrganization($this->getReference(OrganizationAdminFixtures::ORGANIZATION_REFERENCE, Organization::class));
            $service->setName($item['name']);
            $service->setDescription('test');
            $service->setDuration(new DateInterval($item['duration']));
            $service->setEstimatedPrice($item['estimated_price']);
            $service->setCreatedAt(new DateTimeImmutable($item['created_at']));
            $service->setUpdatedAt(new DateTimeImmutable($item['updated_at']));

            $manager->persist($service);
            $this->addReference(self::SERVICE_REFERENCE.$i, $service);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            OrganizationAdminFixtures::class
        ];
    }
}
