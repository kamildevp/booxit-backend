<?php

declare(strict_types=1);

namespace App\DataFixtures\Test\Schedule;

use App\DataFixtures\Test\OrganizationMember\OrganizationAdminFixtures;
use App\Entity\Organization;
use App\Entity\Schedule;
use App\Entity\Service;
use DateInterval;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ScheduleServiceFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $organization = $this->getReference(OrganizationAdminFixtures::ORGANIZATION_REFERENCE, Organization::class);
        $schedule = new Schedule();
        $schedule->setOrganization($organization);
        $schedule->setName('Test Schedule');
        $schedule->setDescription('test');
        $manager->persist($schedule);

        for($i = 1; $i <= 35; $i++){
            $service = new Service();
            $service->setOrganization($organization);
            $service->setName('Test Service ' . $i);
            $service->setDescription('test');
            $service->setDuration(new DateInterval('PT1H'));
            $service->setEstimatedPrice('20.50');
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
