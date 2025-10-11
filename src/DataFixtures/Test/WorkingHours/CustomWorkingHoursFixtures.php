<?php

declare(strict_types=1);

namespace App\DataFixtures\Test\WorkingHours;

use App\DataFixtures\Test\OrganizationMember\OrganizationAdminFixtures;
use App\Entity\CustomTimeWindow;
use App\Entity\Organization;
use App\Entity\Schedule;
use App\Tests\Feature\WorkingHours\DataProvider\GetCustomWorkingHoursDataProvider;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class CustomWorkingHoursFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $organization = $this->getReference(OrganizationAdminFixtures::ORGANIZATION_REFERENCE, Organization::class);
        $schedule = new Schedule();
        $schedule->setOrganization($organization);
        $schedule->setName('Test Schedule');
        $schedule->setDescription('test');
        $manager->persist($schedule);
        
        $data = GetCustomWorkingHoursDataProvider::customWorkingHours();

        foreach($data as $date => $timeWindows){
            foreach($timeWindows as $timeWindow){
                $customTimeWindow = new CustomTimeWindow();
                $customTimeWindow->setDate(DateTimeImmutable::createFromFormat('Y-m-d', $date));
                $customTimeWindow->setStartTime(DateTimeImmutable::createFromFormat('H:i', $timeWindow['start_time']));
                $customTimeWindow->setEndTime(DateTimeImmutable::createFromFormat('H:i', $timeWindow['end_time']));
                $customTimeWindow->setSchedule($schedule);
                $manager->persist($customTimeWindow);
            }
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
