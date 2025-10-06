<?php

declare(strict_types=1);

namespace App\DataFixtures\Test\OrganizationMember;

use App\Entity\Organization;
use App\Entity\OrganizationMember;
use App\Entity\Schedule;
use App\Entity\ScheduleAssignment;
use App\Entity\User;
use App\Enum\Organization\OrganizationRole;
use App\Tests\Utils\DataProvider\ListDataProvider;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class OrganizationMemberScheduleAssignmentsSortingFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $organization = $this->getReference(OrganizationAdminFixtures::ORGANIZATION_REFERENCE, Organization::class);

        $sortedData = ListDataProvider::getSortedColumnsValuesSequence([
            'access_type' => 'schedule_access_type',
            'schedule.name' => 'string'
        ]);

        $data = [
            $sortedData[2],
            $sortedData[0],
            $sortedData[1],
        ];

        $user = new User();
        $user->setName('Test User');
        $user->setEmail("omsa-user@example.com");
        $user->setPassword('dummypass');
        $user->setVerified(true);
        $manager->persist($user);

        $organizationMember = new OrganizationMember();
        $organizationMember->setOrganization($organization);
        $organizationMember->setAppUser($user);
        $organizationMember->setRole(OrganizationRole::MEMBER->value);
        $manager->persist($organizationMember);

        foreach($data as $item){
            $schedule = new Schedule();
            $schedule->setOrganization($organization);
            $schedule->setName($item['schedule.name']);
            $schedule->setDescription('test');
            $manager->persist($schedule);

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
