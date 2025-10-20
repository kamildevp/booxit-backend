<?php

declare(strict_types=1);

namespace App\Tests\Feature\Reservation;

use App\DataFixtures\Test\Availability\AvailabilityFixtures;
use App\Enum\Organization\OrganizationNormalizerGroup;
use App\Enum\Schedule\ScheduleNormalizerGroup;
use App\Enum\Service\ServiceNormalizerGroup;
use App\Repository\ScheduleRepository;
use App\Repository\ServiceRepository;
use App\Tests\Feature\Reservation\DataProvider\ReservationAuthDataProvider;
use App\Tests\Feature\Reservation\DataProvider\ReservationCreateDataProvider;
use App\Tests\Feature\Reservation\DataProvider\UserReservationCreateDataProvider;
use App\Tests\Utils\Attribute\Fixtures;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use App\Tests\Utils\BaseWebTestCase;
use App\Tests\Utils\Trait\EmailConfirmationUtils;
use Symfony\Component\Messenger\Transport\InMemory\InMemoryTransport;

class ReservationControllerTest extends BaseWebTestCase
{
    use EmailConfirmationUtils;

    protected InMemoryTransport $mailerTransport;
    protected ServiceRepository $serviceRepository;
    protected ScheduleRepository $scheduleRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mailerTransport = $this->container->get('messenger.transport.async_mailer');
        $this->serviceRepository = $this->container->get(ServiceRepository::class);
        $this->scheduleRepository = $this->container->get(ScheduleRepository::class);
    }

    #[Fixtures([AvailabilityFixtures::class])]
    #[DataProviderExternal(ReservationCreateDataProvider::class, 'validDataCases')]
    public function testCreate(array $params, array $expectedResponseData): void
    {
        $service = $this->serviceRepository->findOneBy([]);
        $schedule = $this->scheduleRepository->findOneBy([]);
        $params['service_id'] = $service->getId();
        $params['schedule_id'] = $schedule->getId();
        $expectedResponseData['schedule'] = $this->normalizer->normalize($schedule, context: ['groups' => ScheduleNormalizerGroup::BASE_INFO->normalizationGroups()]);
        $expectedResponseData['service'] = $this->normalizer->normalize($service, context: ['groups' => ServiceNormalizerGroup::BASE_INFO->normalizationGroups()]);
        $expectedResponseData['organization'] = $this->normalizer->normalize($schedule->getOrganization(), context: ['groups' => OrganizationNormalizerGroup::BASE_INFO->normalizationGroups()]);

        $responseData = $this->getSuccessfulResponseData($this->client, 'POST', '/api/reservations', $params);
        $this->assertIsInt($responseData['id']);
        $this->assertArrayHasKey('reference', $responseData);
        $this->assertArrayIsEqualToArrayOnlyConsideringListOfKeys($expectedResponseData, $responseData, array_keys($expectedResponseData));
        $this->assertCount(1, $this->mailerTransport->getSent());
    }

    #[DataProviderExternal(ReservationCreateDataProvider::class, 'validationDataCases')]
    public function testCreateValidation(array $params, array $expectedErrors): void
    {
        $this->assertPathValidation($this->client, 'POST', '/api/reservations', $params, $expectedErrors);
        $this->assertCount(0, $this->mailerTransport->getSent());
    }

    #[Fixtures([AvailabilityFixtures::class])]
    #[DataProviderExternal(UserReservationCreateDataProvider::class, 'validDataCases')]
    public function testCreateUserReservation(array $params, array $expectedResponseData): void
    {
        $service = $this->serviceRepository->findOneBy([]);
        $schedule = $this->scheduleRepository->findOneBy([]);
        $params['service_id'] = $service->getId();
        $params['schedule_id'] = $schedule->getId();
        $expectedResponseData['schedule'] = $this->normalizer->normalize($schedule, context: ['groups' => ScheduleNormalizerGroup::BASE_INFO->normalizationGroups()]);
        $expectedResponseData['service'] = $this->normalizer->normalize($service, context: ['groups' => ServiceNormalizerGroup::BASE_INFO->normalizationGroups()]);
        $expectedResponseData['organization'] = $this->normalizer->normalize($schedule->getOrganization(), context: ['groups' => OrganizationNormalizerGroup::BASE_INFO->normalizationGroups()]);

        $this->client->loginUser($this->user, 'api');
        $responseData = $this->getSuccessfulResponseData($this->client, 'POST', '/api/reservations/me', $params);
        $this->assertIsInt($responseData['id']);
        $this->assertArrayHasKey('reference', $responseData);
        $this->assertArrayIsEqualToArrayOnlyConsideringListOfKeys($expectedResponseData, $responseData, array_keys($expectedResponseData));
        $this->assertCount(1, $this->mailerTransport->getSent());
    }

    #[DataProviderExternal(UserReservationCreateDataProvider::class, 'validationDataCases')]
    public function testCreateUserReservationValidation(array $params, array $expectedErrors): void
    {
        $this->client->loginUser($this->user, 'api');
        $this->assertPathValidation($this->client, 'POST', '/api/reservations/me', $params, $expectedErrors);
        $this->assertCount(0, $this->mailerTransport->getSent());
    }

    #[DataProviderExternal(ReservationAuthDataProvider::class, 'protectedPaths')]
    public function testAuthRequirementForProtectedPaths(string $path, string $method): void
    {
        $this->assertPathIsProtected($path, $method);
    }
}
