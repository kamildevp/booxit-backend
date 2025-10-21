<?php

declare(strict_types=1);

namespace App\DataFixtures\Test\Reservation;

use App\DataFixtures\Test\Schedule\ScheduleServiceFixtures;
use App\DataFixtures\Test\ScheduleAssignment\ScheduleAssignmentFixtures;
use App\Entity\EmailConfirmation;
use App\Entity\Reservation;
use App\Entity\Schedule;
use App\Entity\Service;
use App\Enum\EmailConfirmation\EmailConfirmationStatus;
use App\Enum\EmailConfirmation\EmailConfirmationType;
use App\Enum\Reservation\ReservationStatus;
use App\Enum\Reservation\ReservationType;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class CancelReservationByUrlFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(private UserPasswordHasherInterface $hasher)
    {
        
    }

    public function load(ObjectManager $manager): void
    {
        $schedule = $this->getReference(ScheduleAssignmentFixtures::SCHEDULE_REFERENCE, Schedule::class);
        $service = $this->getReference(ScheduleServiceFixtures::SERVICE_REFERENCE.'1', Service::class);

        $reservation = new Reservation();
        $reservation->setSchedule($schedule);
        $reservation->setService($service);
        $reservation->setOrganization($schedule->getOrganization());
        $reservation->setReference("ref");
        $reservation->setEmail("res@example.com");
        $reservation->setPhoneNumber("888888881");
        $reservation->setVerified(false);
        $reservation->setEstimatedPrice($service->getEstimatedPrice());
        $reservation->setStartDateTime((new DateTimeImmutable('wednesday next week'))->setTime(12,0));
        $reservation->setEndDateTime((new DateTimeImmutable('wednesday next week'))->setTime(13,30));
        $reservation->setStatus(ReservationStatus::PENDING->value);
        $reservation->setType(ReservationType::REGULAR->value);
        $manager->persist($reservation);
        $manager->flush();

        $emailConfirmation = new EmailConfirmation();
        $emailConfirmation->setParams(['reservation_id' => $reservation->getId()]);
        $emailConfirmation->setEmail($reservation->getEmail());
        $emailConfirmation->setExpiryDate((new DateTimeImmutable('wednesday next week'))->setTime(12,0));
        $emailConfirmation->setVerificationHandler('test');
        $emailConfirmation->setType(EmailConfirmationType::RESERVATION_CANCELLATION->value);
        $emailConfirmation->setStatus(EmailConfirmationStatus::PENDING->value);
        $manager->persist($emailConfirmation);
        
        $reservation->addEmailConfirmation($emailConfirmation);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ScheduleServiceFixtures::class
        ];
    }
}
