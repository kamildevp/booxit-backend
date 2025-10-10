<?php

declare(strict_types=1);

namespace App\DataFixtures\Test\WorkingHours;

use App\DataFixtures\Test\OrganizationMember\OrganizationAdminFixtures;
use App\Entity\Organization;
use App\Entity\Schedule;
use App\Entity\WeekdayTimeWindow;
use App\Tests\Feature\WorkingHours\DataProvider\GetWeeklyWorkingHoursDataProvider;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class WeeklyWorkingHoursFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $organization = $this->getReference(OrganizationAdminFixtures::ORGANIZATION_REFERENCE, Organization::class);
        $schedule = new Schedule();
        $schedule->setOrganization($organization);
        $schedule->setName('Test Schedule');
        $schedule->setDescription('test');
        
        $data = GetWeeklyWorkingHoursDataProvider::weeklyWorkingHours();

        foreach($data as $weekday => $timeWindows){
            foreach($timeWindows as $timeWindow){
                $weekdayTimeWindow = new WeekdayTimeWindow();
                $weekdayTimeWindow->setWeekday($weekday);
                $weekdayTimeWindow->setStartTime(DateTimeImmutable::createFromFormat('H:i', $timeWindow['start_time']));
                $weekdayTimeWindow->setEndTime(DateTimeImmutable::createFromFormat('H:i', $timeWindow['end_time']));
                $schedule->addWeekdayTimeWindow($weekdayTimeWindow);
            }
        }

        $manager->persist($schedule);
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            OrganizationAdminFixtures::class
        ];
    }
}
