<?php

declare(strict_types=1);

namespace App\DataFixtures\Test\Reservation;

use App\DataFixtures\Test\Schedule\ScheduleServiceFixtures;
use App\DataFixtures\Test\ScheduleAssignment\ScheduleAssignmentFixtures;
use App\Entity\Reservation;
use App\Entity\Schedule;
use App\Entity\Service;
use App\Enum\Reservation\ReservationStatus;
use App\Enum\Reservation\ReservationType;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ReservationFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $schedule = $this->getReference(ScheduleAssignmentFixtures::SCHEDULE_REFERENCE, Schedule::class);
        $startDateTime = (new DateTimeImmutable('monday next week'))->setTime(12,0);

        for ($i = 1; $i <= 35; $i++) {
            $service = $this->getReference(ScheduleServiceFixtures::SERVICE_REFERENCE.$i, Service::class);
            $endDateTime = $startDateTime->add($service->getDuration());

            $reservation = new Reservation();
            $reservation->setSchedule($schedule);
            $reservation->setService($service);
            $reservation->setOrganization($schedule->getOrganization());
            $reservation->setReference("ref$i");
            $reservation->setEmail("res$i@example.com");
            $reservation->setPhoneNumber("88888888$i");
            $reservation->setVerified($i%2 == 0);
            $reservation->setEstimatedPrice((string)$i);
            $reservation->setStartDateTime($startDateTime);
            $reservation->setEndDateTime($endDateTime);
            $reservation->setStatus(ReservationStatus::PENDING->value);
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
