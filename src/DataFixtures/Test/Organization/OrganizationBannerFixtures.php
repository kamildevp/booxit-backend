<?php

declare(strict_types=1);

namespace App\DataFixtures\Test\Organization;

use App\Entity\File;
use App\Entity\Organization;
use App\Enum\File\UploadType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

class OrganizationBannerFixtures extends Fixture
{
    const BANNER_FILE_PATH = '/src/DataFixtures/Test/FileUpload/testImg1.jpg';
    const BANNER_FILE_SIZE = '96176';
    const BANNER_FILE_NAME = 'testImg1.jpg';
    const BANNER_FILE_MIME_TYPE = 'image/jpeg';

    public function __construct(private ContainerBagInterface $containerBag)
    {

    }

    public function load(ObjectManager $manager): void
    {
        $organization = new Organization();
        $organization->setName('Test Organization');
        $organization->setDescription('test');

        $banner = new File();
        $banner->setPath($this->containerBag->get('kernel.project_dir').self::BANNER_FILE_PATH);
        $banner->setSize(self::BANNER_FILE_SIZE);
        $banner->setName(self::BANNER_FILE_NAME);
        $banner->setMimeType(self::BANNER_FILE_MIME_TYPE);
        $banner->setType(UploadType::ORGANIZATION_BANNER->value);
        $manager->persist($banner);

        $organization->setBannerFile($banner);
        $manager->persist($organization);

        $manager->flush();
    }
}
