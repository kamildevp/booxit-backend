<?php

declare(strict_types=1);

namespace App\DataFixtures\Test\OrganizationMember;

use App\DataFixtures\Test\Global\VerifiedUserFixtures;
use App\DataFixtures\Test\User\UserFixtures;
use App\Entity\Organization;
use App\Entity\OrganizationMember;
use App\Entity\User;
use App\Enum\Organization\OrganizationRole;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class OrganizationMemberFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $organization = new Organization();
        $organization->setName('Test Organization');
        $manager->persist($organization);

        $admin = $this->createOrganizationMember($organization, VerifiedUserFixtures::VERIFIED_USER_REFERENCE, OrganizationRole::ADMIN);
        $manager->persist($admin);

        for($i = 1; $i <= 34; $i++){
            $member = $this->createOrganizationMember($organization, UserFixtures::USER_REFERENCE.$i, OrganizationRole::MEMBER);
            $manager->persist($member);
        }

        $manager->flush();
    }

    private function createOrganizationMember(Organization $organization, string $userRef, OrganizationRole $role): OrganizationMember
    {
        $organizationMember = new OrganizationMember();
        $organizationMember->setOrganization($organization);
        $organizationMember->setAppUser($this->getReference($userRef, User::class));
        $organizationMember->setRole($role->value);
        
        return $organizationMember;
    }
}
