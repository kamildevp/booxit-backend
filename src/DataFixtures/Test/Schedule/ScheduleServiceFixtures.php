<?php

declare(strict_types=1);

namespace App\DataFixtures\Test\Schedule;

use App\DataFixtures\Test\OrganizationMember\OrganizationAdminFixtures;
use App\DataFixtures\Test\ScheduleAssignment\ScheduleAssignmentFixtures;
use App\Entity\Organization;
use App\Entity\Schedule;
use App\Entity\Service;
use DateInterval;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ScheduleServiceFixtures extends Fixture implements DependentFixtureInterface
{
    const SERVICE_REFERENCE = 'ss-service';

    public function load(ObjectManager $manager): void
    {
        $organization = $this->getReference(OrganizationAdminFixtures::ORGANIZATION_REFERENCE, Organization::class);
        $schedule = $this->getReference(ScheduleAssignmentFixtures::SCHEDULE_REFERENCE, Schedule::class);

        for($i = 1; $i <= 35; $i++){
            $service = new Service();
            $service->setOrganization($organization);
            $service->setName('Test Service ' . $i);
            $service->setDescription('test');
            $service->setDuration(new DateInterval($i%2 == 0 ? 'PT1H' : 'PT30M'));
            $service->setEstimatedPrice('20.50');
            $manager->persist($service);

            $schedule->addService($service);
            $this->addReference(self::SERVICE_REFERENCE.$i, $service);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ScheduleAssignmentFixtures::class
        ];
    }
}
