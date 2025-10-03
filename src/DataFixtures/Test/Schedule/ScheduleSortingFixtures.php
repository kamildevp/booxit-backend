<?php

declare(strict_types=1);

namespace App\DataFixtures\Test\Schedule;

use App\DataFixtures\Test\OrganizationMember\OrganizationAdminFixtures;
use App\Entity\Organization;
use App\Entity\Schedule;
use App\Tests\Utils\DataProvider\ListDataProvider;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ScheduleSortingFixtures extends Fixture
{    
    const SERVICE_REFERENCE = 'schedule-sort';

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

        foreach($data as $i => $item){
            $schedule = new Schedule();
            $schedule->setOrganization($this->getReference(OrganizationAdminFixtures::ORGANIZATION_REFERENCE, Organization::class));
            $schedule->setName($item['name']);
            $schedule->setDescription('test');
            $schedule->setCreatedAt(new DateTimeImmutable($item['created_at']));
            $schedule->setUpdatedAt(new DateTimeImmutable($item['updated_at']));

            $manager->persist($schedule);
            $this->addReference(self::SERVICE_REFERENCE.$i, $schedule);
        }

        $manager->flush();
    }
}
