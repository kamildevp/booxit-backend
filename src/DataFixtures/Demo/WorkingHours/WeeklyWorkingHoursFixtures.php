<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo\WorkingHours;

use App\DataFixtures\Demo\Schedule\ScheduleFixtures;
use App\DataFixtures\Demo\WorkingHours\DataProvider\WeeklyWorkingHoursDataProvider;
use App\Entity\Schedule;
use App\Entity\WeekdayTimeWindow;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class WeeklyWorkingHoursFixtures extends Fixture implements FixtureGroupInterface, DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $data = WeeklyWorkingHoursDataProvider::getData();

        for($i = 1; $i <= 32; $i++){
            $schedule = $this->getReference("schedule$i", Schedule::class);
            foreach($data as $weekday => $timeWindows){
                foreach($timeWindows as $timeWindow){
                    $weekdayTimeWindow = new WeekdayTimeWindow();
                    $weekdayTimeWindow->setWeekday($weekday);
                    $weekdayTimeWindow->setStartTime(DateTimeImmutable::createFromFormat('H:i', $timeWindow['start_time']));
                    $weekdayTimeWindow->setEndTime(DateTimeImmutable::createFromFormat('H:i', $timeWindow['end_time']));
                    $schedule->addWeekdayTimeWindow($weekdayTimeWindow);
                }
            }
        }

        $manager->persist($schedule);
        $manager->flush();
    }

    public static function getGroups(): array
    {
        return [
            'demo'
        ];
    }

    public function getDependencies(): array
    {
        return [
            ScheduleFixtures::class
        ];
    }
}
