<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo\ScheduleService;

use App\DataFixtures\Demo\Schedule\ScheduleFixtures;
use App\DataFixtures\Demo\ScheduleService\DataProvider\ScheduleServiceDataProvider;
use App\DataFixtures\Demo\Service\ServiceFixtures;
use App\Entity\Schedule;
use App\Entity\Service;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ScheduleServiceFixtures extends Fixture implements FixtureGroupInterface, DependentFixtureInterface
{    
    public function load(ObjectManager $manager): void
    {
        $data = ScheduleServiceDataProvider::getData();


        foreach($data as $i => $item){
            $schedule = $this->getReference($item['schedule_reference'], Schedule::class);
            $service = $this->getReference($item['service_reference'], Service::class);
            $schedule->addService($service);
            $manager->persist($schedule);
        }

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
            ServiceFixtures::class,
            ScheduleFixtures::class,
        ];
    }
}
