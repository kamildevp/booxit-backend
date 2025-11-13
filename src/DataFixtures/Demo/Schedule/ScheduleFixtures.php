<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo\Schedule;

use App\DataFixtures\Demo\Organization\OrganizationFixtures;
use App\DataFixtures\Demo\Schedule\DataProvider\ScheduleDataProvider;
use App\Entity\Organization;
use App\Entity\Schedule;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ScheduleFixtures extends Fixture implements FixtureGroupInterface, DependentFixtureInterface
{    
    public function load(ObjectManager $manager): void
    {
        $data = ScheduleDataProvider::getData();

        foreach($data as $i => $item){
            $schedule = new Schedule();
            $schedule->setOrganization($this->getReference($item['organization_reference'], Organization::class));
            $schedule->setName($item['name']);
            $schedule->setDescription($item['description']);
            $schedule->setCreatedAt(new DateTimeImmutable($item['created_at']));
            $schedule->setUpdatedAt(new DateTimeImmutable($item['updated_at']));

            $manager->persist($schedule);
            $this->addReference(('schedule').($i+1), $schedule);
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
            OrganizationFixtures::class,
        ];
    }
}
