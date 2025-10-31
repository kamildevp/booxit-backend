<?php

declare(strict_types=1);

namespace App\DataFixtures\Test\WorkingHours;

use App\DataFixtures\Test\ScheduleAssignment\ScheduleAssignmentFixtures;
use App\Entity\CustomTimeWindow;
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
        $schedule = $this->getReference(ScheduleAssignmentFixtures::SCHEDULE_REFERENCE, Schedule::class);
        
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
            ScheduleAssignmentFixtures::class
        ];
    }
}
