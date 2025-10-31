<?php

declare(strict_types=1);

namespace App\Tests\Utils;

use App\DataFixtures\Test\Global\VerifiedUserFixtures;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Tests\Utils\Attribute\Fixtures;
use App\Tests\Utils\Trait\DataFormattingTestTools;
use App\Tests\Utils\Trait\FileTestTools;
use App\Tests\Utils\Trait\ListAssertions;
use App\Tests\Utils\Trait\RequestTestTools;
use App\Tests\Utils\Trait\ResponseAssertions;
use App\Tests\Utils\Trait\ResponseTestTools;
use App\Tests\Utils\Trait\ValidationAssertions;
use Doctrine\ORM\EntityManagerInterface;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Psr\Container\ContainerInterface;
use ReflectionMethod;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class BaseWebTestCase extends WebTestCase 
{
    use RequestTestTools,ResponseTestTools, DataFormattingTestTools, FileTestTools; 
    use ResponseAssertions, ValidationAssertions, ListAssertions;

    protected KernelBrowser $client;
    protected ContainerInterface $container;
    protected AbstractDatabaseTool $dbTool;
    protected string $secret;
    protected string $storageDir;
    protected string $projectDir;
    protected NormalizerInterface $normalizer;
    protected User $user;
    protected EntityManagerInterface $entityManager;
    protected Filesystem $fs;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
        $this->container = $this->getContainer();
        $this->dbTool = $this->container->get(DatabaseToolCollection::class)->get();
        $this->secret = $this->container->get(ContainerBagInterface::class)->get('kernel.secret');
        $this->storageDir = $this->container->get(ContainerBagInterface::class)->get('storage_directory');
        $this->projectDir = $this->container->get(ContainerBagInterface::class)->get('kernel.project_dir');
        $this->normalizer = $this->container->get(NormalizerInterface::class);
        $this->entityManager = $this->container->get(EntityManagerInterface::class);
        $this->fs = new Filesystem();

        $this->loadFixtures();
        $this->installExtensions();

        $userRepository = $this->container->get(UserRepository::class);
        $this->user = $userRepository->findOneBy(['email' => VerifiedUserFixtures::VERIFIED_USER_EMAIL]) ?? new User();
    }

    protected function getFixturesAttribute(): ?Fixtures
    {
        $fixturesAttributes = (new ReflectionMethod($this, $this->name()))->getAttributes(Fixtures::class);
        if(empty($fixturesAttributes)){
            return null;
        }

        return $fixturesAttributes[0]->newInstance();
    }

    protected function loadFixtures(): void
    {
        $fixturesAttribute = $this->getFixturesAttribute();
        if($fixturesAttribute && !$fixturesAttribute->append){
            $this->dbTool->loadFixtures($fixturesAttribute->fixtures);
            return;
        }

        $this->dbTool->loadFixtures([VerifiedUserFixtures::class, ...($fixturesAttribute->fixtures ?? [])]);
    }

    protected function installExtensions(): void
    {
        $conn = $this->entityManager->getConnection();
        $conn->executeStatement('CREATE EXTENSION IF NOT EXISTS earthdistance CASCADE');
    }
}