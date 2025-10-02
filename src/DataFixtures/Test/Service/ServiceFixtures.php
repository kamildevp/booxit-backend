<?php

declare(strict_types=1);

namespace App\DataFixtures\Test\Service;

use App\DataFixtures\Test\OrganizationMember\OrganizationAdminFixtures;
use App\Entity\Organization;
use App\Entity\Service;;
use DateInterval;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ServiceFixtures extends Fixture
{
    const SERVICE_REFERENCE = 'service';

    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i <= 35; $i++) {
            $service = new Service();
            $service->setOrganization($this->getReference(OrganizationAdminFixtures::ORGANIZATION_REFERENCE, Organization::class));
            $service->setName('Test Service ' . $i);
            $service->setDescription('test');
            $service->setDuration(new DateInterval('PT30M'));
            $service->setEstimatedPrice('16.70');

            $manager->persist($service);
            $this->addReference(self::SERVICE_REFERENCE.$i, $service);
        }

        $manager->flush();
    }
}
