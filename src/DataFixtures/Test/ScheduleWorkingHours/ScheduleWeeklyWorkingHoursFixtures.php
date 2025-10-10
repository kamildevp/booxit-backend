<?php

declare(strict_types=1);

namespace App\DataFixtures\Test\ScheduleWorkingHours;

use App\DataFixtures\Test\OrganizationMember\OrganizationAdminFixtures;
use App\Entity\Organization;
use App\Entity\OrganizationMember;
use App\Entity\Schedule;
use App\Entity\ScheduleAssignment;
use App\Entity\User;
use App\Entity\WeekdayTimeWindow;
use App\Enum\Organization\OrganizationRole;
use App\Enum\Schedule\ScheduleAccessType;
use App\Tests\Feature\ScheduleWorkingHours\DataProvider\ScheduleGetWeeklyWorkingHoursDataProvider;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ScheduleWeeklyWorkingHoursFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $organization = $this->getReference(OrganizationAdminFixtures::ORGANIZATION_REFERENCE, Organization::class);
        $schedule = new Schedule();
        $schedule->setOrganization($organization);
        $schedule->setName('Test Schedule');
        $schedule->setDescription('test');
        
        $data = ScheduleGetWeeklyWorkingHoursDataProvider::scheduleWeeklyWorkingHours();

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
