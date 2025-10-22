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

class ConfirmReservationConflictFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $schedule = $this->getReference(ScheduleAssignmentFixtures::SCHEDULE_REFERENCE, Schedule::class);
        $startDateTime = (new DateTimeImmutable('monday next week'))->setTime(12,0);

        $service = $this->getReference(ScheduleServiceFixtures::SERVICE_REFERENCE.'1', Service::class);
        $endDateTime = $startDateTime->add($service->getDuration());
        $reservedBy = $this->getReference(UserFixtures::USER_REFERENCE.'1', User::class);

        $reservation = new Reservation();
        $reservation->setSchedule($schedule);
        $reservation->setService($service);
        $reservation->setOrganization($schedule->getOrganization());
        $reservation->setReference("ref$");
        $reservation->setEmail("user1@example.com");
        $reservation->setPhoneNumber("888888881");
        $reservation->setVerified(true);
        $reservation->setEstimatedPrice('42.00');
        $reservation->setStartDateTime($startDateTime);
        $reservation->setEndDateTime($endDateTime);
        $reservation->setStatus(ReservationStatus::CONFIRMED->value);
        $reservation->setType(ReservationType::REGULAR->value);
        $reservation->setReservedBy($reservedBy);
        $manager->persist($reservation);

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
