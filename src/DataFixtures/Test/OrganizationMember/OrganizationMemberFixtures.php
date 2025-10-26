<?php

declare(strict_types=1);

namespace App\DataFixtures\Test\OrganizationMember;

use App\Entity\Organization;
use App\Entity\OrganizationMember;
use App\Entity\User;
use App\Enum\Organization\OrganizationRole;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class OrganizationMemberFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $organization = $this->getReference(OrganizationAdminFixtures::ORGANIZATION_REFERENCE, Organization::class);

        for($i = 1; $i <= 34; $i++){
            $user = new User();
            $user->setName('Test User ' . $i);
            $user->setEmail("om-user{$i}@example.com");
            $user->setUsername("om_user{$i}");
            $user->setPassword('dummypass');
            $user->setVerified(true);
            $manager->persist($user);

            $organizationMember = new OrganizationMember();
            $organizationMember->setOrganization($organization);
            $organizationMember->setAppUser($user);
            $organizationMember->setRole(OrganizationRole::MEMBER->value);
            $manager->persist($organizationMember);
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
