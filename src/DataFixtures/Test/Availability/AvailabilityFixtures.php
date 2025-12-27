<?php

declare(strict_types=1);

namespace App\DataFixtures\Test\Availability;

use App\DataFixtures\Test\ScheduleService\ScheduleServiceFixtures;
use App\DataFixtures\Test\ScheduleAssignment\ScheduleAssignmentFixtures;
use App\Entity\CustomTimeWindow;
use App\Entity\Reservation;
use App\Entity\Schedule;
use App\Entity\Service;
use App\Entity\WeekdayTimeWindow;
use App\Enum\Reservation\ReservationStatus;
use App\Enum\Weekday;
use App\Enum\Reservation\ReservationType;
use DateTimeImmutable;
use DateTimeZone;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class AvailabilityFixtures extends Fixture implements DependentFixtureInterface
{   
    private DateTimeZone $defaultTimezone;

    public function __construct(#[Autowire('%timezone%')]private string $defaultTimezoneString)
    {
        $this->defaultTimezone = new DateTimeZone($defaultTimezoneString);
    }

    public function load(ObjectManager $manager): void
    {
        $schedule = $this->getReference(ScheduleAssignmentFixtures::SCHEDULE_REFERENCE, Schedule::class);
        $timezone = new DateTimeZone($schedule->getTimezone());
        $startDate = new DateTimeImmutable('monday next week', $timezone);
        $endDate = new DateTimeImmutable('wednesday next week', $timezone);

        $weeklyWorkingHours = [
            Weekday::MONDAY->value => ['start_time' => '00:00', 'end_time' => '02:00'],
            Weekday::TUESDAY->value => ['start_time' => '00:00', 'end_time' => '02:00'],
            Weekday::WEDNESDAY->value => ['start_time' => '00:00', 'end_time' => '02:00'],
        ];

        $customWorkingHours = [
            $startDate->format('Y-m-d') => ['start_time' => '23:00', 'end_time' => '00:00'],
            $endDate->format('Y-m-d') => ['start_time' => '00:00', 'end_time' => '04:00']
        ];

        $reservations = [
            [   
                'start_date_time' => $startDate->setTime(23,30)->setTimezone($this->defaultTimezone), 
                'end_date_time' => $startDate->modify('+1 day')->setTime(0,0)->setTimezone($this->defaultTimezone), 
                'verified' => false, 
                'status' => ReservationStatus::PENDING->value, 
            ],
            [
                'start_date_time' => $startDate->modify('+1 day')->setTime(0,30)->setTimezone($this->defaultTimezone),
                'end_date_time' => $startDate->modify('+1 day')->setTime(1,30)->setTimezone($this->defaultTimezone),  
                'verified' => true, 
                'status' => ReservationStatus::CONFIRMED->value, 
            ],
            [
                'start_date_time' => $endDate->setTime(1,0)->setTimezone($this->defaultTimezone), 
                'end_date_time' => $endDate->setTime(1,30)->setTimezone($this->defaultTimezone), 
                'verified' => true, 
                'status' => ReservationStatus::CONFIRMED->value, 
            ],
            [
                'start_date_time' => $endDate->setTime(2,0)->setTimezone($this->defaultTimezone), 
                'end_date_time' => $endDate->setTime(2,30)->setTimezone($this->defaultTimezone), 
                'verified' => true, 
                'status' => ReservationStatus::CUSTOMER_CANCELLED->value, 
                'duration' => 'PT30M'
            ],
            [
                'start_date_time' => $endDate->setTime(3,0)->setTimezone($this->defaultTimezone), 
                'end_date_time' => $endDate->setTime(3,30)->setTimezone($this->defaultTimezone), 
                'verified' => true, 
                'status' => ReservationStatus::ORGANIZATION_CANCELLED->value, 
            ],
        ];

        foreach($weeklyWorkingHours as $weekday => $data){
            $weekdayTimeWindow = new WeekdayTimeWindow();
            $weekdayTimeWindow->setSchedule($schedule);
            $weekdayTimeWindow->setWeekday($weekday);
            $weekdayTimeWindow->setStartTime(new DateTimeImmutable($data['start_time']));
            $weekdayTimeWindow->setEndTime(new DateTimeImmutable($data['end_time']));
            $manager->persist($weekdayTimeWindow);
        }

        foreach($customWorkingHours as $date => $data){
            $customTimeWindow = new CustomTimeWindow();
            $customTimeWindow->setSchedule($schedule);
            $startDateTime = (new DateTimeImmutable($date.' '.$data['start_time'], $timezone))->setTimezone($this->defaultTimezone);
            $endDateTime = (new DateTimeImmutable($date.' '.$data['end_time'], $timezone))->setTimezone($this->defaultTimezone);
            $endDateTime = $endDateTime <= $startDateTime ? $endDateTime->modify('+1 day') : $endDateTime;

            $customTimeWindow->setStartDateTime($startDateTime);
            $customTimeWindow->setEndDateTime($endDateTime);
            $manager->persist($customTimeWindow);
        }

        foreach($reservations as $indx => $data){
            $i = $indx+1;
            $reservation = new Reservation();
            $reservation->setSchedule($schedule);
            $service = $this->getReference(ScheduleServiceFixtures::SERVICE_REFERENCE.$i, Service::class);
            $reservation->setService($service);
            $reservation->setOrganization($schedule->getOrganization());
            $reservation->setReference("ref$i");
            $reservation->setEmail("res$i@example.com");
            $reservation->setPhoneNumber("88888888$i");
            $reservation->setVerified($data['verified']);
            $reservation->setEstimatedPrice((string)$i);
            $reservation->setStartDateTime($data['start_date_time']);
            $reservation->setEndDateTime($data['end_date_time']);
            $reservation->setStatus($data['status']);
            $reservation->setType(ReservationType::REGULAR->value);
            $manager->persist($reservation);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ScheduleServiceFixtures::class
        ];
    }
}
