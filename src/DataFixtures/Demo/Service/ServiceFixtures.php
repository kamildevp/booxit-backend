<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo\Service;

use App\DataFixtures\Demo\Organization\OrganizationFixtures;
use App\DataFixtures\Demo\Service\DataProvider\ServiceDataProvider;
use App\Entity\Organization;
use App\Entity\Service;
use DateInterval;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ServiceFixtures extends Fixture implements FixtureGroupInterface, DependentFixtureInterface
{    
    public function load(ObjectManager $manager): void
    {
        $data = ServiceDataProvider::getData();

        foreach($data as $i => $item){
            $service = new Service();
            $service->setOrganization($this->getReference($item['organization_reference'], Organization::class));
            $service->setName($item['name']);
            $service->setCategory($item['category']);
            $service->setDescription($item['description']);
            $service->setDuration(new DateInterval($item['duration']));
            $service->setEstimatedPrice((string)$item['estimated_price']);
            $service->setCreatedAt(new DateTimeImmutable($item['created_at']));
            $service->setUpdatedAt(new DateTimeImmutable($item['updated_at']));

            $manager->persist($service);
            $this->addReference('service'.($i+1), $service);
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
