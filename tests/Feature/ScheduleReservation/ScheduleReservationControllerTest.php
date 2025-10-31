<?php

declare(strict_types=1);

namespace App\Tests\Feature\ScheduleReservation;

use App\DataFixtures\Test\Availability\AvailabilityFixtures;
use App\DataFixtures\Test\OrganizationMember\OrganizationMemberFixtures;
use App\DataFixtures\Test\Reservation\CancelReservationConflictFixtures;
use App\DataFixtures\Test\ScheduleReservation\ConfirmScheduleReservationConflictFixtures;
use App\DataFixtures\Test\ScheduleReservation\ScheduleReservationFixtures;
use App\DataFixtures\Test\ScheduleReservation\ScheduleReservationSortingFixtures;
use App\DataFixtures\Test\User\UserFixtures;
use App\DataFixtures\Test\UserReservation\UserReservationSortingFixtures;
use App\Enum\Organization\OrganizationNormalizerGroup;
use App\Enum\Reservation\ReservationNormalizerGroup;
use App\Enum\Schedule\ScheduleNormalizerGroup;
use App\Enum\Service\ServiceNormalizerGroup;
use App\Repository\ReservationRepository;
use App\Repository\ScheduleRepository;
use App\Repository\ServiceRepository;
use App\Repository\UserRepository;
use App\Response\ForbiddenResponse;
use App\Tests\Feature\ScheduleReservation\DataProvider\ScheduleReservationAuthDataProvider;
use App\Tests\Feature\ScheduleReservation\DataProvider\ScheduleReservationConfirmDataProvider;
use App\Tests\Feature\ScheduleReservation\DataProvider\ScheduleReservationCreateCustomDataProvider;
use App\Tests\Feature\ScheduleReservation\DataProvider\ScheduleReservationCreateDataProvider;
use App\Tests\Feature\ScheduleReservation\DataProvider\ScheduleReservationListDataProvider;
use App\Tests\Feature\ScheduleReservation\DataProvider\ScheduleReservationNotFoundDataProvider;
use App\Tests\Feature\ScheduleReservation\DataProvider\ScheduleReservationPatchDataProvider;
use App\Tests\Utils\Attribute\Fixtures;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use App\Tests\Utils\BaseWebTestCase;
use Symfony\Component\Messenger\Transport\InMemory\InMemoryTransport;

class ScheduleReservationControllerTest extends BaseWebTestCase
{
    protected InMemoryTransport $mailerTransport;
    protected ServiceRepository $serviceRepository;
    protected ScheduleRepository $scheduleRepository;
    protected ReservationRepository $reservationRepository;
    protected UserRepository $userRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mailerTransport = $this->container->get('messenger.transport.async_mailer');
        $this->serviceRepository = $this->container->get(ServiceRepository::class);
        $this->scheduleRepository = $this->container->get(ScheduleRepository::class);
        $this->reservationRepository = $this->container->get(ReservationRepository::class);
        $this->userRepository = $this->container->get(UserRepository::class);
    }

    #[Fixtures([AvailabilityFixtures::class])]
    #[DataProviderExternal(ScheduleReservationCreateDataProvider::class, 'validDataCases')]
    public function testCreate(array $params, array $expectedResponseData): void
    {
        $schedule = $this->scheduleRepository->findOneBy([]);
        $scheduleId = $schedule->getId();
        $service = $this->serviceRepository->findOneBy([]);
        $params['service_id'] = $service->getId();
        $expectedResponseData['schedule'] = $this->normalizer->normalize($schedule, context: ['groups' => ScheduleNormalizerGroup::BASE_INFO->normalizationGroups()]);
        $expectedResponseData['service'] = $this->normalizer->normalize($service, context: ['groups' => ServiceNormalizerGroup::BASE_INFO->normalizationGroups()]);
        $expectedResponseData['organization'] = $this->normalizer->normalize($schedule->getOrganization(), context: ['groups' => OrganizationNormalizerGroup::BASE_INFO->normalizationGroups()]);

        $responseData = $this->getSuccessfulResponseData($this->client, 'POST', "/api/schedules/$scheduleId/reservations", $params);
        $this->assertIsInt($responseData['id']);
        $this->assertArrayHasKey('reference', $responseData);
        $this->assertArrayIsEqualToArrayOnlyConsideringListOfKeys($expectedResponseData, $responseData, array_keys($expectedResponseData));
        $this->assertCount(1, $this->mailerTransport->getSent());
    }

    #[Fixtures([AvailabilityFixtures::class])]
    #[DataProviderExternal(ScheduleReservationCreateDataProvider::class, 'validationDataCases')]
    public function testCreateValidation(array $params, array $expectedErrors): void
    {
        $scheduleId = $this->scheduleRepository->findOneBy([])->getId();
        $this->assertPathValidation($this->client, 'POST', "/api/schedules/$scheduleId/reservations", $params, $expectedErrors);
        $this->assertCount(0, $this->mailerTransport->getSent());
    }

    #[Fixtures([ScheduleReservationFixtures::class])]
    #[DataProviderExternal(ScheduleReservationCreateCustomDataProvider::class, 'validDataCases')]
    public function testCreateCustom(array $params, array $expectedResponseData): void
    {
        $scheduleId = $this->scheduleRepository->findOneBy([])->getId();
        $service = $this->serviceRepository->findOneBy([]);
        $params['service_id'] = $service->getId();
        $expectedResponseData['service'] = $this->normalizer->normalize($service, context: ['groups' => ServiceNormalizerGroup::BASE_INFO->normalizationGroups()]);

        $user = $this->userRepository->findOneBy(['email' => 'sa-user1@example.com']);
        $this->client->loginUser($user, 'api');
        $responseData = $this->getSuccessfulResponseData($this->client, 'POST', "/api/schedules/$scheduleId/reservations/custom", $params);
        $this->assertIsInt($responseData['id']);
        $this->assertArrayHasKey('reference', $responseData);   
        $this->assertArrayHasKey('reserved_by', $responseData);
        $this->assertArrayHasKey('created_by', $responseData);
        $this->assertArrayHasKey('updated_by', $responseData);
        $this->assertArrayIsEqualToArrayOnlyConsideringListOfKeys($expectedResponseData, $responseData, array_keys($expectedResponseData));
    }

    #[Fixtures([ScheduleReservationFixtures::class])]
    #[DataProviderExternal(ScheduleReservationCreateCustomDataProvider::class, 'validationDataCases')]
    public function testCreateCustomValidation(array $params, array $expectedErrors): void
    {
        $scheduleId = $this->scheduleRepository->findOneBy([])->getId();
        $user = $this->userRepository->findOneBy(['email' => 'sa-user1@example.com']);
        $this->client->loginUser($user, 'api');
        $this->assertPathValidation($this->client, 'POST', "/api/schedules/$scheduleId/reservations/custom", $params, $expectedErrors);
    }

    #[Fixtures([ScheduleReservationFixtures::class])]
    public function testCancel(): void
    {
        $reservation = $this->reservationRepository->findOneBy([]);
        $reservationId = $reservation->getId();
        $scheduleId = $reservation->getSchedule()->getId();
        $user = $this->userRepository->findOneBy(['email' => 'sa-user1@example.com']);
        $this->client->loginUser($user, 'api');
        $responseData = $this->getSuccessfulResponseData($this->client, 'POST', "/api/schedules/$scheduleId/reservations/$reservationId/cancel");
        $this->assertEquals('Reservation has been cancelled', $responseData['message']);
        $this->assertCount(1, $this->mailerTransport->getSent());
    }

    #[Fixtures([CancelReservationConflictFixtures::class])]
    public function testCancelConflict(): void
    {
        $reservation = $this->reservationRepository->findOneBy([]);
        $reservationId = $reservation->getId();
        $scheduleId = $reservation->getSchedule()->getId();
        $user = $this->userRepository->findOneBy(['email' => 'sa-user1@example.com']);

        $this->client->loginUser($user, 'api');
        $responseData = $this->getFailureResponseData($this->client, 'POST', "/api/schedules/$scheduleId/reservations/$reservationId/cancel", expectedCode: 409);
        $this->assertEquals('Reservation has already been cancelled.', $responseData['message']);
        $this->assertCount(0, $this->mailerTransport->getSent());
    }

    #[Fixtures([ScheduleReservationFixtures::class])]
    #[DataProviderExternal(ScheduleReservationConfirmDataProvider::class, 'validDataCases')]
    public function testConfirm(array $params): void
    {
        $reservation = $this->reservationRepository->findOneBy([]);
        $reservationId = $reservation->getId();
        $scheduleId = $reservation->getSchedule()->getId();
        $user = $this->userRepository->findOneBy(['email' => 'sa-user1@example.com']);
        $this->client->loginUser($user, 'api');
        $responseData = $this->getSuccessfulResponseData($this->client, 'POST', "/api/schedules/$scheduleId/reservations/$reservationId/confirm", $params);
        $this->assertEquals(['message' => 'Reservation has been confirmed'], $responseData);
        $this->assertCount(1, $this->mailerTransport->getSent());
    }

    #[Fixtures([ScheduleReservationFixtures::class])]
    #[DataProviderExternal(ScheduleReservationConfirmDataProvider::class, 'validationDataCases')]
    public function testConfirmValidation(array $params, array $expectedErrors): void
    {
        $reservation = $this->reservationRepository->findOneBy([]);
        $reservationId = $reservation->getId();
        $scheduleId = $reservation->getSchedule()->getId();
        $this->client->loginUser($this->user, 'api');
        $this->assertPathValidation($this->client, 'POST', "/api/schedules/$scheduleId/reservations/$reservationId/confirm", $params, $expectedErrors);
        $this->assertCount(0, $this->mailerTransport->getSent());
    }

    #[Fixtures([ConfirmScheduleReservationConflictFixtures::class])]
    #[DataProviderExternal(ScheduleReservationConfirmDataProvider::class, 'validDataCases')]
    public function testConfirmConflict(array $params): void
    {
        $reservation = $this->reservationRepository->findOneBy([]);
        $reservationId = $reservation->getId();
        $scheduleId = $reservation->getSchedule()->getId();
        $user = $this->userRepository->findOneBy(['email' => 'sa-user1@example.com']);
        $this->client->loginUser($user, 'api');
        $responseData = $this->getFailureResponseData($this->client, 'POST', "/api/schedules/$scheduleId/reservations/$reservationId/confirm", $params, expectedCode: 409);
        $this->assertEquals('Reservation has already been confirmed.', $responseData['message']);
        $this->assertCount(0, $this->mailerTransport->getSent());
    }

    #[Fixtures([ScheduleReservationFixtures::class])]
    public function testGet(): void
    {
        $reservation = $this->reservationRepository->findOneBy([]);
        $reservationId = $reservation->getId();
        $scheduleId = $reservation->getSchedule()->getId();
        $expectedResponseData = $this->normalize($reservation, ReservationNormalizerGroup::SCHEDULE_RESERVATIONS->normalizationGroups());
        $user = $this->userRepository->findOneBy(['email' => 'sa-user2@example.com']);
        $this->client->loginUser($user, 'api');
        $responseData = $this->getSuccessfulResponseData($this->client, 'GET', "/api/schedules/$scheduleId/reservations/$reservationId");

        $this->assertEquals($expectedResponseData, $responseData);
    }

    #[Fixtures([ScheduleReservationFixtures::class])]
    #[DataProviderExternal(ScheduleReservationPatchDataProvider::class, 'validDataCases')]
    public function testPatch(array $params, array $expectedResponseData, bool $emailSent): void
    {
        $reservation = $this->reservationRepository->findOneBy([]);
        $reservationId = $reservation->getId();
        $scheduleId = $reservation->getSchedule()->getId();
        $service = $this->serviceRepository->findOneBy([]);
        $params['service_id'] = $service->getId();
        $expectedResponseData['service'] = $this->normalizer->normalize($service, context: ['groups' => ServiceNormalizerGroup::BASE_INFO->normalizationGroups()]);

        $user = $this->userRepository->findOneBy(['email' => 'sa-user1@example.com']);
        $this->client->loginUser($user, 'api');

        $responseData = $this->getSuccessfulResponseData($this->client, 'PATCH', "/api/schedules/$scheduleId/reservations/$reservationId", $params);
        $this->assertIsInt($responseData['id']);
        $this->assertArrayHasKey('reference', $responseData);
        $this->assertArrayHasKey('reserved_by', $responseData);
        $this->assertArrayHasKey('created_by', $responseData);
        $this->assertArrayHasKey('updated_by', $responseData);
        $this->assertArrayIsEqualToArrayOnlyConsideringListOfKeys($expectedResponseData, $responseData, array_keys($expectedResponseData));
        $this->assertCount($emailSent ? 1 : 0, $this->mailerTransport->getSent());
    }

    #[Fixtures([ScheduleReservationFixtures::class])]
    #[DataProviderExternal(ScheduleReservationPatchDataProvider::class, 'validationDataCases')]
    public function testPatchValidation(array $params, array $expectedErrors): void
    {
        $reservation = $this->reservationRepository->findOneBy([]);
        $reservationId = $reservation->getId();
        $scheduleId = $reservation->getSchedule()->getId();
        
        $this->client->loginUser($this->user, 'api');
        $this->assertPathValidation($this->client, 'PATCH', "/api/schedules/$scheduleId/reservations/$reservationId", $params, $expectedErrors);
        $this->assertCount(0, $this->mailerTransport->getSent());
    }

    #[Fixtures([ScheduleReservationFixtures::class])]
    public function testDelete(): void
    {
        $reservation = $this->reservationRepository->findOneBy([]);
        $reservationId = $reservation->getId();
        $scheduleId = $reservation->getSchedule()->getId();
        $this->client->loginUser($this->user, 'api');
        $responseData = $this->getSuccessfulResponseData($this->client, 'DELETE', "/api/schedules/$scheduleId/reservations/$reservationId");

        $this->assertEquals('Reservation has been removed', $responseData['message']);
    }

    #[Fixtures([ScheduleReservationFixtures::class])]
    #[DataProviderExternal(ScheduleReservationListDataProvider::class, 'listDataCases')]
    public function testList(int $page, int $perPage, int $total): void
    {
        $scheduleId = $this->scheduleRepository->findOneBy([])->getId();
        $path = "/api/schedules/$scheduleId/reservations?" . http_build_query([
            'page' => $page,
            'per_page' => $perPage,
        ]);
        $user = $this->userRepository->findOneBy(['email' => 'sa-user2@example.com']);
        $this->client->loginUser($user, 'api');
        $responseData = $this->getSuccessfulResponseData($this->client, 'GET', $path);

        $offset = ($page - 1) * $perPage;
        $items = $this->reservationRepository->findBy([], ['id' => 'ASC'], $perPage, $offset);
        $formattedItems = $this->normalize($items, ReservationNormalizerGroup::SCHEDULE_RESERVATIONS->normalizationGroups());

        $this->assertPaginatorResponse($responseData, $page, $perPage, $total, $formattedItems);
    }

    #[Fixtures([ScheduleReservationSortingFixtures::class])]
    #[DataProviderExternal(ScheduleReservationListDataProvider::class, 'filtersDataCases')]
    public function testListFilters(array $filters, array $expectedItemData): void
    {
        $mappedFilters = [];
        foreach($filters as $paramName => $value){
            $mappedFilters[$paramName] = match($paramName){
                'service_id' => [$this->serviceRepository->findOneBy(['name' => $value])->getId()],
                'reserved_by_id' => [$this->userRepository->findOneBy(['name' => $value])->getId()],
                default => $value
            };
        }

        $scheduleId = $this->scheduleRepository->findOneBy([])->getId();
        $path = "/api/schedules/$scheduleId/reservations?" . http_build_query(['filters' => $mappedFilters]);
        $user = $this->userRepository->findOneBy(['email' => 'sa-user2@example.com']);
        $this->client->loginUser($user, 'api');
        $responseData = $this->getSuccessfulResponseData($this->client, 'GET', $path);

        $this->assertCount(1, $responseData['items']);
        $dotExpectedItemData = array_dot($expectedItemData);
        $dotResponseItemData = array_dot($responseData['items'][0]);
        $this->assertArrayIsEqualToArrayOnlyConsideringListOfKeys($dotExpectedItemData, $dotResponseItemData, array_keys($dotExpectedItemData));
    }

    #[Fixtures([ScheduleReservationSortingFixtures::class])]
    #[DataProviderExternal(ScheduleReservationListDataProvider::class, 'sortingDataCases')]
    public function testListSorting(string $sorting, array $orderedItems): void
    {
        $scheduleId = $this->scheduleRepository->findOneBy([])->getId();
        $path = "/api/schedules/$scheduleId/reservations?" . http_build_query(['order' => $sorting]);
        $user = $this->userRepository->findOneBy(['email' => 'sa-user2@example.com']);
        $this->client->loginUser($user, 'api');
        $responseData = $this->getSuccessfulResponseData($this->client, 'GET', $path);

        $this->assertGreaterThanOrEqual(count($orderedItems), count($responseData['items']));
        foreach($orderedItems as $indx => $item){
            $this->assertArrayIsEqualToArrayOnlyConsideringListOfKeys($item, $responseData['items'][$indx], array_keys($item));
        }
    }

    #[Fixtures([ScheduleReservationSortingFixtures::class])]
    #[DataProviderExternal(ScheduleReservationListDataProvider::class, 'validationDataCases')]
    public function testListValidation(array $params, array $expectedErrors): void
    {
        $scheduleId = $this->scheduleRepository->findOneBy([])->getId();
        $user = $this->userRepository->findOneBy(['email' => 'sa-user2@example.com']);
        $this->client->loginUser($user, 'api');
        $path = "/api/schedules/$scheduleId/reservations?" . http_build_query($params);
        $this->assertPathValidation($this->client, 'GET', $path, [], $expectedErrors);
    }

    #[Fixtures([ScheduleReservationFixtures::class])]
    #[DataProviderExternal(ScheduleReservationNotFoundDataProvider::class, 'dataCases')]
    public function testNotFoundResponses(string $path, string $method, string $expectedMessage): void
    {
        $scheduleId = $this->scheduleRepository->findOneBy([])->getId();
        $path = str_replace('{schedule}', (string)$scheduleId, $path);
        $this->client->loginUser($this->user, 'api');
        $responseData = $this->getFailureResponseData($this->client, $method, $path, expectedCode: 404);
        $this->assertEquals($expectedMessage, $responseData['message']);
    }

    #[Fixtures([ScheduleReservationFixtures::class])]
    #[DataProviderExternal(ScheduleReservationAuthDataProvider::class, 'protectedPaths')]
    public function testAuthRequirementForProtectedPaths(string $path, string $method): void
    {
        $reservation = $this->reservationRepository->findOneBy([]);
        $reservationId = $reservation->getId();
        $scheduleId = $reservation->getSchedule()->getId();
        $path = str_replace('{schedule}', (string)$scheduleId, $path);
        $path = str_replace('{reservation}', (string)$reservationId, $path);
        $this->assertPathIsProtected($path, $method);
    }

    #[Fixtures([UserFixtures::class, OrganizationMemberFixtures::class, ScheduleReservationFixtures::class])]
    #[DataProviderExternal(ScheduleReservationAuthDataProvider::class, 'privilegesOnlyPaths')]
    public function testPrivilegesRequirementForProtectedPaths(string $path, string $method, string $userEmail, array $parameters = []): void
    {
        $reservation = $this->reservationRepository->findOneBy([]);
        $reservationId = $reservation->getId();
        $scheduleId = $reservation->getSchedule()->getId();
        $path = str_replace('{schedule}', (string)$scheduleId, $path);
        $path = str_replace('{reservation}', (string)$reservationId, $path);
        $parameters = array_map(fn($val) => $val == '{schedule}' ? $scheduleId : $val, $parameters);
        $user = $this->userRepository->findOneBy(['email' => $userEmail]);

        $this->client->loginUser($user, 'api');
        $responseData = $this->getFailureResponseData($this->client, $method, $path, expectedCode: 403);
        $this->assertEquals(ForbiddenResponse::RESPONSE_MESSAGE, $responseData['message']);
    }
}
