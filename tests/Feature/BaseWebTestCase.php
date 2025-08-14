<?php

namespace App\Tests\Feature;

use App\DataFixtures\Test\Global\VerifiedUserFixtures;
use App\Entity\User;
use App\Tests\Feature\Trait\DataFormattingTestTools;
use App\Tests\Feature\Trait\ListAssertions;
use App\Tests\Feature\Trait\RequestTestTools;
use App\Tests\Feature\Trait\ResponseAssertions;
use App\Tests\Feature\Trait\ResponseTestTools;
use App\Tests\Feature\Trait\ValidationAssertions;
use Doctrine\ORM\EntityManagerInterface;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class BaseWebTestCase extends WebTestCase 
{
    use RequestTestTools,ResponseTestTools, DataFormattingTestTools; 
    use ResponseAssertions, ValidationAssertions, ListAssertions;

    protected KernelBrowser $client;
    protected ContainerInterface $container;
    protected AbstractDatabaseTool $dbTool;
    protected string $secret;
    protected NormalizerInterface $normalizer;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
        $this->container = $this->getContainer();
        $this->dbTool = $this->container->get(DatabaseToolCollection::class)->get();
        $this->secret = $this->container->get(ContainerBagInterface::class)->get('kernel.secret');
        $this->normalizer = $this->container->get(NormalizerInterface::class);

        $this->dbTool->loadFixtures([
            VerifiedUserFixtures::class
        ]);
        $userRepository = $this->container->get(EntityManagerInterface::class)->getRepository(User::class);
        $this->user = $userRepository->findOneBy(['email' => 'verifieduser@example.com']);
    }
}