<?php

declare(strict_types=1);

namespace App\DataFixtures\Test\Schedule;

use App\DataFixtures\Test\OrganizationMember\OrganizationAdminFixtures;
use App\Entity\Organization;
use App\Entity\Schedule;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ScheduleFixtures extends Fixture
{
    const SERVICE_REFERENCE = 'schedule';

    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i <= 35; $i++) {
            $schedule = new Schedule();
            $schedule->setOrganization($this->getReference(OrganizationAdminFixtures::ORGANIZATION_REFERENCE, Organization::class));
            $schedule->setName('Test Schedule ' . $i);
            $schedule->setDescription('test');

            $manager->persist($schedule);
            $this->addReference(self::SERVICE_REFERENCE.$i, $schedule);
        }

        $manager->flush();
    }
}
