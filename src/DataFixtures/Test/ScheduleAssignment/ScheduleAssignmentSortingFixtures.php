<?php

declare(strict_types=1);

namespace App\DataFixtures\Test\ScheduleAssignment;

use App\DataFixtures\Test\OrganizationMember\OrganizationAdminFixtures;
use App\Entity\Organization;
use App\Entity\OrganizationMember;
use App\Entity\Schedule;
use App\Entity\ScheduleAssignment;
use App\Entity\User;
use App\Tests\Utils\DataProvider\ListDataProvider;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ScheduleAssignmentSortingFixtures extends Fixture implements DependentFixtureInterface
{    
    public function load(ObjectManager $manager): void
    {
        $sortedData = ListDataProvider::getSortedColumnsValuesSequence([
            'access_type' => 'schedule_access_type',
            'organization_member.role' => 'organization_role',
            'organization_member.app_user.name' => 'string',
        ]);

        $data = [
            $sortedData[2],
            $sortedData[0],
            $sortedData[1],
        ];

        $organization = $this->getReference(OrganizationAdminFixtures::ORGANIZATION_REFERENCE, Organization::class);
        $schedule = new Schedule();
        $schedule->setOrganization($organization);
        $schedule->setName('Test Schedule');
        $schedule->setDescription('test');
        $manager->persist($schedule);

        foreach($data as $i => $item){
            $user = new User();
            $user->setName($item['organization_member.app_user.name']);
            $user->setEmail("sa-user{$i}@example.com");
            $user->setPassword('hashed_pass');
            $user->setVerified(true);
            $manager->persist($user);

            $organizationMember = new OrganizationMember();
            $organizationMember->setOrganization($organization);
            $organizationMember->setAppUser($user);
            $organizationMember->setRole($item['organization_member.role']);
            $manager->persist($organizationMember);

            $scheduleAssignment = new ScheduleAssignment();
            $scheduleAssignment->setSchedule($schedule);
            $scheduleAssignment->setOrganizationMember($organizationMember);
            $scheduleAssignment->setAccessType($item['access_type']);
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
