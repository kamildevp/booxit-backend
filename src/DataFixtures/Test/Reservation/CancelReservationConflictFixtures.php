<?php

declare(strict_types=1);

namespace App\DataFixtures\Test\Reservation;

use App\DataFixtures\Test\Schedule\ScheduleServiceFixtures;
use App\DataFixtures\Test\ScheduleAssignment\ScheduleAssignmentFixtures;
use App\DataFixtures\Test\User\UserFixtures;
use App\Entity\Reservation;
use App\Entity\Schedule;
use App\Entity\Service;
use App\Entity\User;
use App\Enum\Reservation\ReservationStatus;
use App\Enum\Reservation\ReservationType;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class CancelReservationConflictFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $schedule = $this->getReference(ScheduleAssignmentFixtures::SCHEDULE_REFERENCE, Schedule::class);
        $startDateTime = (new DateTimeImmutable('monday next week'))->setTime(12,0);

        for ($i = 1; $i <= 35; $i++) {
            $service = $this->getReference(ScheduleServiceFixtures::SERVICE_REFERENCE.$i, Service::class);
            $endDateTime = $startDateTime->add($service->getDuration());
            $reservedBy = $this->getReference(UserFixtures::USER_REFERENCE.$i, User::class);

            $reservation = new Reservation();
            $reservation->setSchedule($schedule);
            $reservation->setService($service);
            $reservation->setOrganization($schedule->getOrganization());
            $reservation->setReference("ref$i");
            $reservation->setEmail("user$i@example.com");
            $reservation->setPhoneNumber("88888888$i");
            $reservation->setVerified($i%2 == 0);
            $reservation->setEstimatedPrice((string)$i);
            $reservation->setStartDateTime($startDateTime);
            $reservation->setEndDateTime($endDateTime);
            $reservation->setStatus(ReservationStatus::ORGANIZATION_CANCELLED->value);
            $reservation->setType(ReservationType::REGULAR->value);
            $reservation->setReservedBy($reservedBy);
            $manager->persist($reservation);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ScheduleServiceFixtures::class,
            UserFixtures::class,
        ];
    }
}
