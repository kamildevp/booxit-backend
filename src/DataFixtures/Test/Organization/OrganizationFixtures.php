<?php

declare(strict_types=1);

namespace App\DataFixtures\Test\Organization;

use App\DataFixtures\Test\Global\VerifiedUserFixtures;
use App\Entity\Organization;
use App\Entity\OrganizationMember;
use App\Entity\User;
use App\Enum\Organization\OrganizationRole;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class OrganizationFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i <= 35; $i++) {
            $organization = new Organization();
            $organization->setName('Test Organization ' . $i);
            $organization->setDescription('test');
            $manager->persist($organization);

            $organizationMember = new OrganizationMember();
            $organizationMember->setOrganization($organization);
            $organizationMember->setAppUser($this->getReference(VerifiedUserFixtures::VERIFIED_USER_REFERENCE, User::class));
            $organizationMember->setRole(OrganizationRole::ADMIN->value);
            $manager->persist($organizationMember);
        }

        $manager->flush();
    }
}
