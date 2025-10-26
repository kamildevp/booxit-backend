<?php

declare(strict_types=1);

namespace App\DataFixtures\Test\ScheduleReservation;

use App\DataFixtures\Test\ScheduleAssignment\ScheduleAssignmentFixtures;
use App\Entity\Reservation;
use App\Entity\Schedule;
use App\Entity\Service;
use App\Entity\User;
use App\Tests\Utils\DataProvider\ListDataProvider;
use DateInterval;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ScheduleReservationSortingFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $schedule = $this->getReference(ScheduleAssignmentFixtures::SCHEDULE_REFERENCE, Schedule::class);

        $sortedData = ListDataProvider::getSortedColumnsValuesSequence([
            'service_name' => 'string',
            'user_name' => 'string',
            'reference' => 'string',
            'email' => 'string',
            'phone_number' => 'string',
            'verified' => 'boolean',
            'expiry_date' => 'datetime',
            'estimated_price' => 'decimal',
            'start_date_time' => 'datetime',
            'end_date_time' => 'datetime',
            'type' => 'reservation_type',
            'status' => 'reservation_status',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ]);

        $data = [
            $sortedData[2],
            $sortedData[0],
            $sortedData[1],
        ];

        foreach($data as $i => $item){
            $service = new Service();
            $service->setOrganization($schedule->getOrganization());
            $service->setName($item['service_name']);
            $service->setDescription("test");
            $service->setEstimatedPrice($item['estimated_price']);
            $service->setDuration(new DateInterval('PT1H'));
            $manager->persist($service);
            $schedule->addService($service);
            $manager->persist($schedule);

            $user = new User();
            $user->setName($item['user_name']);
            $user->setEmail("user{$i}@example.com");
            $user->setUsername("user{$i}");
            $user->setPassword('dummy');
            $user->setVerified(true);
            $manager->persist($user);

            $reservation = new Reservation();
            $reservation->setSchedule($schedule);
            $reservation->setService($service);
            $reservation->setOrganization($schedule->getOrganization());
            $reservation->setReference($item['reference']);
            $reservation->setEmail($item['email']);
            $reservation->setPhoneNumber($item['phone_number']);
            $reservation->setVerified($item['verified']);
            $reservation->setEstimatedPrice($item['estimated_price']);
            $reservation->setStartDateTime(new DateTimeImmutable($item['start_date_time']));
            $reservation->setEndDateTime(new DateTimeImmutable($item['end_date_time']));
            $reservation->setExpiryDate(new DateTimeImmutable($item['expiry_date']));
            $reservation->setStatus($item['status']);
            $reservation->setType($item['type']);
            $reservation->setReservedBy($user);
            $reservation->setCreatedAt(new DateTimeImmutable($item['created_at']));
            $reservation->setUpdatedAt(new DateTimeImmutable($item['updated_at']));
            $manager->persist($reservation);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ScheduleAssignmentFixtures::class,
        ];
    }
}
