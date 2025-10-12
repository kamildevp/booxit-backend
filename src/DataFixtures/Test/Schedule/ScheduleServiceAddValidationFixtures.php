<?php

declare(strict_types=1);

namespace App\DataFixtures\Test\Schedule;

use App\Entity\Organization;
use App\Entity\OrganizationMember;
use App\Entity\Service;
use App\Entity\User;
use App\Enum\Organization\OrganizationRole;
use DateInterval;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ScheduleServiceAddValidationFixtures extends Fixture
{
    const ORGANIZATION_NAME = 'schedule-service-add-conflict-organization';
    const USER_NAME = 'schedule-service-add-conflict-user';
    const USER_EMAIL = 'schedule-service-add-user@example.com';
    const SERVICE_NAME = 'schedule-service-add-conflict-service';

    public function load(ObjectManager $manager): void
    {
        $organization = new Organization();
        $organization->setName(self::ORGANIZATION_NAME);
        $organization->setDescription('test');
        $manager->persist($organization);

        $user = new User();
        $user->setName(self::USER_NAME);
        $user->setEmail(self::USER_EMAIL);
        $user->setPassword('hashed_pass');
        $user->setVerified(true);
        $manager->persist($user);

        $organizationMember = new OrganizationMember();
        $organizationMember->setOrganization($organization);
        $organizationMember->setAppUser($user);
        $organizationMember->setRole(OrganizationRole::ADMIN->value);
        $manager->persist($organizationMember);

        $service = new Service();
        $service->setOrganization($organization);
        $service->setName(self::SERVICE_NAME);
        $service->setDescription('test');
        $service->setDuration(new DateInterval('PT1H'));
        $service->setEstimatedPrice('20.50');
        $manager->persist($service);

        $manager->flush();
    }
}
