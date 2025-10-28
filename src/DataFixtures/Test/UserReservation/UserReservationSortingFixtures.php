<?php

declare(strict_types=1);

namespace App\DataFixtures\Test\UserReservation;

use App\DataFixtures\Test\User\UserFixtures;
use App\Entity\Embeddable\Address;
use App\Entity\Organization;
use App\Entity\Reservation;
use App\Entity\Schedule;
use App\Entity\Service;
use App\Entity\User;
use App\Enum\Reservation\ReservationType;
use App\Enum\Service\ServiceCategory;
use App\Tests\Utils\DataProvider\ListDataProvider;
use DateInterval;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class UserReservationSortingFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $reservedBy = $this->getReference(UserFixtures::USER_REFERENCE.'1', User::class);

        $sortedData = ListDataProvider::getSortedColumnsValuesSequence([
            'organization_name' => 'string',
            'schedule_name' => 'string',
            'service_name' => 'string',
            'reference' => 'string',
            'estimated_price' => 'decimal',
            'start_date_time' => 'datetime',
            'end_date_time' => 'datetime',
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
            $organization = new Organization();
            $organization->setName($item['organization_name']);
            $organization->setDescription("test");
            $address = new Address();
            $address->setStreet('Test street');
            $address->setCity('Test city');
            $address->setRegion('Test region');
            $address->setPostalCode('30-126');
            $address->setCountry('Test country');
            $address->setPlaceId('TestPlaceId');
            $address->setFormattedAddress('Test address');
            $address->setLatitude(50);
            $address->setLongitude(19);
            $organization->setAddress($address);
            $manager->persist($organization);

            $service = new Service();
            $service->setOrganization($organization);
            $service->setName($item['service_name']);
            $service->setCategory(ServiceCategory::BARBER->value);
            $service->setDescription("test");
            $service->setEstimatedPrice('25.50');
            $service->setDuration(new DateInterval('PT1H'));
            $manager->persist($service);

            $schedule = new Schedule();
            $schedule->setOrganization($organization);
            $schedule->setName($item['schedule_name']);
            $schedule->setDescription("test");
            $schedule->addService($service);
            $manager->persist($schedule);

            $reservation = new Reservation();
            $reservation->setSchedule($schedule);
            $reservation->setService($service);
            $reservation->setOrganization($organization);
            $reservation->setReference($item['reference']);
            $reservation->setEmail($reservedBy->getEmail());
            $reservation->setPhoneNumber("888888881");
            $reservation->setVerified(true);
            $reservation->setEstimatedPrice($item['estimated_price']);
            $reservation->setStartDateTime(new DateTimeImmutable($item['start_date_time']));
            $reservation->setEndDateTime(new DateTimeImmutable($item['end_date_time']));
            $reservation->setStatus($item['status']);
            $reservation->setType(ReservationType::REGULAR->value);
            $reservation->setReservedBy($reservedBy);
            $reservation->setCreatedAt(new DateTimeImmutable($item['created_at']));
            $reservation->setUpdatedAt(new DateTimeImmutable($item['updated_at']));
            $manager->persist($reservation);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }
}
