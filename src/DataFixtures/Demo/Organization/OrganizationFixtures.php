<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo\Organization;

use App\DataFixtures\Demo\Organization\DataProvider\OrganizationDataProvider;
use App\Entity\Embeddable\Address;
use App\Entity\File;
use App\Entity\Organization;
use App\Enum\File\UploadType;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

class OrganizationFixtures extends Fixture implements FixtureGroupInterface
{    
    const BANNER_SOURCE_FILE_PATH = '/src/DataFixtures/Demo/Organization/DataProvider/banner.png';
    const BANNER_FILE_PATH = '/src/storage/organization/banner/fixture-banner.png';
    const BANNER_FILE_SIZE = '96176';
    const BANNER_FILE_NAME = 'banner.png';
    const BANNER_FILE_MIME_TYPE = 'image/png';

    public function __construct(private ContainerBagInterface $containerBag)
    {

    }

    public function load(ObjectManager $manager): void
    {
        $data = OrganizationDataProvider::getData();

        foreach($data as $i => $item){
            $organization = new Organization();
            $organization->setName($item['name']);
            $organization->setDescription($item['description']);
            $address = new Address();
            $address->setStreet($item['address_street']);
            $address->setCity($item['address_city']);
            $address->setRegion($item['address_region']);
            $address->setPostalCode($item['address_postal_code']);
            $address->setCountry($item['address_country']);
            $address->setPlaceId($item['place_id']);
            $address->setFormattedAddress($item['formatted_address']);
            $address->setLatitude($item['address_latitude']);
            $address->setLongitude($item['address_longitude']);
            $organization->setAddress($address);
            $organization->setCreatedAt(new DateTimeImmutable($item['created_at']));
            $organization->setUpdatedAt(new DateTimeImmutable($item['updated_at']));
            $manager->persist($organization);

            $projectDir = $this->containerBag->get('kernel.project_dir');
            $banner = new File();
            $banner->setPath($projectDir.self::BANNER_FILE_PATH);
            $banner->setSize(self::BANNER_FILE_SIZE);
            $banner->setName(self::BANNER_FILE_NAME);
            $banner->setMimeType(self::BANNER_FILE_MIME_TYPE);
            $banner->setType(UploadType::ORGANIZATION_BANNER->value);
            $manager->persist($banner);

            $organization->setBannerFile($banner);
            $this->setReference("organization".($i+1), $organization);
        }

        $manager->flush();
    }

    public static function getGroups(): array
    {
        return [
            'demo'
        ];
    }
}
