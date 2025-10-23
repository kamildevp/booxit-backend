<?php

declare(strict_types=1);

namespace App\DataFixtures\Test\UserReservation;

use App\DataFixtures\Test\ScheduleService\ScheduleServiceFixtures;
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

class UserReservationFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $schedule = $this->getReference(ScheduleAssignmentFixtures::SCHEDULE_REFERENCE, Schedule::class);
        $startDateTime = (new DateTimeImmutable('monday next week'))->setTime(12,0);
        $reservedBy = $this->getReference(UserFixtures::USER_REFERENCE.'1', User::class);

        for ($i = 1; $i <= 35; $i++) {
            $service = $this->getReference(ScheduleServiceFixtures::SERVICE_REFERENCE.$i, Service::class);
            $endDateTime = $startDateTime->add($service->getDuration());

            $reservation = new Reservation();
            $reservation->setSchedule($schedule);
            $reservation->setService($service);
            $reservation->setOrganization($schedule->getOrganization());
            $reservation->setReference("ref$i");
            $reservation->setEmail("user1@example.com");
            $reservation->setPhoneNumber("888888881");
            $reservation->setVerified($i%2 == 0);
            $reservation->setEstimatedPrice((string)$i);
            $reservation->setStartDateTime($startDateTime);
            $reservation->setEndDateTime($endDateTime);
            $reservation->setStatus(ReservationStatus::PENDING->value);
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
