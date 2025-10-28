<?php

declare(strict_types=1);

namespace App\DataFixtures\Test\OrganizationMember;

use App\DataFixtures\Test\Global\VerifiedUserFixtures;
use App\Entity\Embeddable\Address;
use App\Entity\Organization;
use App\Entity\OrganizationMember;
use App\Entity\User;
use App\Enum\Organization\OrganizationRole;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class OrganizationAdminFixtures extends Fixture
{
    const ORGANIZATION_NAME = 'Test Organization';
    const ORGANIZATION_REFERENCE = 'organization';

    public function load(ObjectManager $manager): void
    {
        $organization = new Organization();
        $organization->setName(self::ORGANIZATION_NAME);
        $address = new Address();
        $address->setStreet('Test street');
        $address->setCity('Test city');
        $address->setRegion('Test region');
        $address->setPostalCode('30-126');
        $address->setCountry('Test country');
        $address->setPlaceId('TestPlaceId');
        $address->setFormattedAddress('Test address');
        $address->setLatitude(50);
        $address->setLongitude(19);
        $organization->setAddress($address);
        $manager->persist($organization);

        $admin = new OrganizationMember();
        $admin->setOrganization($organization);
        $admin->setAppUser($this->getReference(VerifiedUserFixtures::VERIFIED_USER_REFERENCE, User::class));
        $admin->setRole(OrganizationRole::ADMIN->value);

        $manager->persist($admin);
        $manager->flush();

        $this->addReference(self::ORGANIZATION_REFERENCE, $organization);
    }
}
