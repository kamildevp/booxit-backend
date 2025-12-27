<?php

declare(strict_types=1);

namespace App\DataFixtures\Test\WorkingHours;

use App\DataFixtures\Test\ScheduleAssignment\ScheduleAssignmentFixtures;
use App\Entity\CustomTimeWindow;
use App\Entity\Schedule;
use App\Tests\Feature\WorkingHours\DataProvider\GetCustomWorkingHoursDataProvider;
use DateTimeImmutable;
use DateTimeZone;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class CustomWorkingHoursFixtures extends Fixture implements DependentFixtureInterface
{
    private DateTimeZone $defaultTimezone;

    public function __construct(#[Autowire('%timezone%')]private string $defaultTimezoneString)
    {
        $this->defaultTimezone = new DateTimeZone($defaultTimezoneString);
    }

    public function load(ObjectManager $manager): void
    {
        $schedule = $this->getReference(ScheduleAssignmentFixtures::SCHEDULE_REFERENCE, Schedule::class);
        
        $data = GetCustomWorkingHoursDataProvider::customWorkingHours();

        foreach($data as $date => $timeWindows){
            foreach($timeWindows as $timeWindow){
                $customTimeWindow = new CustomTimeWindow();
                $timezone = new DateTimeZone('Europe/Warsaw');
                $customTimeWindow->setStartDateTime(DateTimeImmutable::createFromFormat('Y-m-d H:i', $date.' '.$timeWindow['start_time'], $timezone)->setTimezone($this->defaultTimezone));
                $customTimeWindow->setEndDateTime(DateTimeImmutable::createFromFormat('Y-m-d H:i', $date.' '.$timeWindow['end_time'], $timezone)->setTimezone($this->defaultTimezone));
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
