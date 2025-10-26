<?php

declare(strict_types=1);

namespace App\DataFixtures\Test\ScheduleAssignment;

use App\DataFixtures\Test\OrganizationMember\OrganizationAdminFixtures;
use App\Entity\Organization;
use App\Entity\OrganizationMember;
use App\Entity\User;
use App\Enum\Organization\OrganizationRole;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ScheduleAssignmentValidationFixtures extends Fixture implements DependentFixtureInterface
{
    const ORGANIZATION_NAME = 'schedule-assignment-conflict-organization';
    const USER_NAME = 'schedule-assignment-conflict-user';
    const USER_EMAIL = 'sa-conflict-user@example.com';

    public function load(ObjectManager $manager): void
    {
        $organization = new Organization();
        $organization->setName(self::ORGANIZATION_NAME);
        $organization->setDescription('test');
        $manager->persist($organization);

        $user = new User();
        $user->setName(self::USER_NAME);
        $user->setEmail(self::USER_EMAIL);
        $user->setUsername("sa_conflict_user");
        $user->setPassword('hashed_pass');
        $user->setVerified(true);
        $manager->persist($user);

        $organizationMember = new OrganizationMember();
        $organizationMember->setOrganization($organization);
        $organizationMember->setAppUser($user);
        $organizationMember->setRole(OrganizationRole::MEMBER->value);
        $manager->persist($organizationMember);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            OrganizationAdminFixtures::class
        ];
    }
}
