<?php

declare(strict_types=1);

namespace App\DataFixtures\Test\OrganizationMember;

use App\Entity\Organization;
use App\Entity\OrganizationMember;
use App\Entity\Schedule;
use App\Entity\ScheduleAssignment;
use App\Entity\User;
use App\Enum\Organization\OrganizationRole;
use App\Enum\Schedule\ScheduleAccessType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class OrganizationMemberScheduleAssignmentsFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $organization = $this->getReference(OrganizationAdminFixtures::ORGANIZATION_REFERENCE, Organization::class);

        $user = new User();
        $user->setName('Test User');
        $user->setEmail("omsa-user@example.com");
        $user->setUsername("omsa_user");
        $user->setPassword('dummypass');
        $user->setVerified(true);
        $manager->persist($user);

        $organizationMember = new OrganizationMember();
        $organizationMember->setOrganization($organization);
        $organizationMember->setAppUser($user);
        $organizationMember->setRole(OrganizationRole::MEMBER->value);
        $manager->persist($organizationMember);

        for($i = 1; $i <= 35; $i++){
            $schedule = new Schedule();
            $schedule->setOrganization($organization);
            $schedule->setName('Test Schedule ' . $i);
            $schedule->setDescription('test');
            $manager->persist($schedule);

            $scheduleAssignment = new ScheduleAssignment();
            $scheduleAssignment->setSchedule($schedule);
            $scheduleAssignment->setOrganizationMember($organizationMember);
            $scheduleAssignment->setAccessType(ScheduleAccessType::READ->value);
            $manager->persist($scheduleAssignment);
        }

        $manager->flush();
    }
    
    public function getDependencies(): array
    {
        return [
            OrganizationAdminFixtures::class
        ];
    }
}
