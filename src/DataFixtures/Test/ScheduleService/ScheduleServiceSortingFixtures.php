<?php

declare(strict_types=1);

namespace App\DataFixtures\Test\ScheduleService;

use App\DataFixtures\Test\OrganizationMember\OrganizationAdminFixtures;
use App\Entity\Organization;
use App\Entity\Schedule;
use App\Entity\Service;
use App\Tests\Utils\DataProvider\ListDataProvider;
use DateInterval;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ScheduleServiceSortingFixtures extends Fixture implements DependentFixtureInterface
{    
    public function load(ObjectManager $manager): void
    {
        $sortedData = ListDataProvider::getSortedColumnsValuesSequence([
            'name' => 'string',
            'category' => 'service_category',
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

        $organization = $this->getReference(OrganizationAdminFixtures::ORGANIZATION_REFERENCE, Organization::class);
        $schedule = new Schedule();
        $schedule->setOrganization($organization);
        $schedule->setName('Test Schedule');
        $schedule->setDescription('test');
        $manager->persist($schedule);

        foreach($data as $i => $item){
            $service = new Service();
            $service->setOrganization($organization);
            $service->setName($item['name']);
            $service->setCategory($item['category']);
            $service->setDescription('test');
            $service->setDuration(new DateInterval($item['duration']));
            $service->setEstimatedPrice($item['estimated_price']);
            $service->setCreatedAt(new DateTimeImmutable($item['created_at']));
            $service->setUpdatedAt(new DateTimeImmutable($item['updated_at']));
            $manager->persist($service);

            $schedule->addService($service);
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
