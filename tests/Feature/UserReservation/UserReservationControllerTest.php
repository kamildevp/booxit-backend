<?php

declare(strict_types=1);

namespace App\Tests\Feature\UserReservation;

use App\DataFixtures\Test\Availability\AvailabilityFixtures;
use App\DataFixtures\Test\Reservation\CancelReservationConflictFixtures;
use App\DataFixtures\Test\Reservation\ReservationFixtures;
use App\DataFixtures\Test\UserReservation\UserReservationFixtures;
use App\DataFixtures\Test\UserReservation\UserReservationSortingFixtures;
use App\Enum\Organization\OrganizationNormalizerGroup;
use App\Enum\Reservation\ReservationNormalizerGroup;
use App\Enum\Schedule\ScheduleNormalizerGroup;
use App\Enum\Service\ServiceNormalizerGroup;
use App\Repository\OrganizationRepository;
use App\Repository\ReservationRepository;
use App\Repository\ScheduleRepository;
use App\Repository\ServiceRepository;
use App\Repository\UserRepository;
use App\Tests\Feature\UserReservation\DataProvider\UserReservationNotFoundDataProvider;
use App\Tests\Feature\UserReservation\DataProvider\UserReservationAuthDataProvider;
use App\Tests\Feature\UserReservation\DataProvider\UserReservationCreateDataProvider;
use App\Tests\Feature\UserReservation\DataProvider\UserReservationListDataProvider;
use App\Tests\Utils\Attribute\Fixtures;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use App\Tests\Utils\BaseWebTestCase;
use App\Tests\Utils\Trait\EmailConfirmationUtils;
use Symfony\Component\Messenger\Transport\InMemory\InMemoryTransport;

class UserReservationControllerTest extends BaseWebTestCase
{
    use EmailConfirmationUtils;

    protected InMemoryTransport $mailerTransport;
    protected ServiceRepository $serviceRepository;
    protected ScheduleRepository $scheduleRepository;
    protected ReservationRepository $reservationRepository;
    protected UserRepository $userRepository;
    protected OrganizationRepository $organizationRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mailerTransport = $this->container->get('messenger.transport.async_mailer');
        $this->serviceRepository = $this->container->get(ServiceRepository::class);
        $this->scheduleRepository = $this->container->get(ScheduleRepository::class);
        $this->reservationRepository = $this->container->get(ReservationRepository::class);
        $this->userRepository = $this->container->get(UserRepository::class);
        $this->organizationRepository = $this->container->get(OrganizationRepository::class);
    }

    #[Fixtures([AvailabilityFixtures::class])]
    #[DataProviderExternal(UserReservationCreateDataProvider::class, 'validDataCases')]
    public function testCreate(array $params, array $expectedResponseData): void
    {
        $service = $this->serviceRepository->findOneBy([]);
        $schedule = $this->scheduleRepository->findOneBy([]);
        $params['service_id'] = $service->getId();
        $params['schedule_id'] = $schedule->getId();
        $expectedResponseData['schedule'] = $this->normalizer->normalize($schedule, context: ['groups' => ScheduleNormalizerGroup::BASE_INFO->normalizationGroups()]);
        $expectedResponseData['service'] = $this->normalizer->normalize($service, context: ['groups' => ServiceNormalizerGroup::BASE_INFO->normalizationGroups()]);
        $expectedResponseData['organization'] = $this->normalizer->normalize($schedule->getOrganization(), context: ['groups' => OrganizationNormalizerGroup::BASE_INFO->normalizationGroups()]);

        $this->client->loginUser($this->user, 'api');
        $responseData = $this->getSuccessfulResponseData($this->client, 'POST', '/api/users/me/reservations', $params);
        $this->assertIsInt($responseData['id']);
        $this->assertArrayHasKey('reference', $responseData);
        $this->assertArrayIsEqualToArrayOnlyConsideringListOfKeys($expectedResponseData, $responseData, array_keys($expectedResponseData));
        $this->assertCount(1, $this->mailerTransport->getSent());
    }

    #[DataProviderExternal(UserReservationCreateDataProvider::class, 'validationDataCases')]
    public function testCreateValidation(array $params, array $expectedErrors): void
    {
        $this->client->loginUser($this->user, 'api');
        $this->assertPathValidation($this->client, 'POST', '/api/users/me/reservations', $params, $expectedErrors);
        $this->assertCount(0, $this->mailerTransport->getSent());
    }

    #[Fixtures([ReservationFixtures::class])]
    public function testCancel(): void
    {
        $reservation = $this->reservationRepository->findOneBy([]);
        $reservationId = $reservation->getId();
        $user = $reservation->getReservedBy();
        $this->client->loginUser($user, 'api');
        $responseData = $this->getSuccessfulResponseData($this->client, 'POST', "/api/users/me/reservations/$reservationId/cancel");
        $this->assertEquals('Reservation has been cancelled', $responseData['message']);
        $this->assertCount(1, $this->mailerTransport->getSent());
    }

    #[Fixtures([CancelReservationConflictFixtures::class])]
    public function testCancelConflict(): void
    {
        $reservation = $this->reservationRepository->findOneBy([]);
        $reservationId = $reservation->getId();
        $user = $reservation->getReservedBy();
        $this->client->loginUser($user, 'api');
        $responseData = $this->getFailureResponseData($this->client, 'POST', "/api/users/me/reservations/$reservationId/cancel", expectedCode: 409);
        $this->assertEquals('Reservation has already been cancelled.', $responseData['message']);
        $this->assertCount(0, $this->mailerTransport->getSent());
    }

    #[Fixtures([ReservationFixtures::class])]
    public function testCancelForReservationNotLinkedToUserAccount(): void
    {
        $reservationId = $this->reservationRepository->findOneBy(['reference' => 'ref1'])->getId();
        $user = $this->userRepository->findOneBy(['email' => 'user10@example.com']);
        $this->client->loginUser($user, 'api');
        $responseData = $this->getFailureResponseData($this->client, 'POST', "/api/users/me/reservations/$reservationId/cancel", expectedCode: 404);
        $this->assertEquals('Reservation not found', $responseData['message']);
    }

    #[Fixtures([ReservationFixtures::class])]
    public function testGet(): void
    {
        $reservation = $this->reservationRepository->findOneBy([]);
        $reservationId = $reservation->getId();
        $expectedResponseData = $this->normalize($reservation, ReservationNormalizerGroup::USER_RESERVATIONS->normalizationGroups());
        $user = $reservation->getReservedBy();
        $this->client->loginUser($user, 'api');
        $responseData = $this->getSuccessfulResponseData($this->client, 'GET', "/api/users/me/reservations/$reservationId");

        $this->assertEquals($expectedResponseData, $responseData);
    }

    #[Fixtures([ReservationFixtures::class])]
    public function testGetForReservationNotLinkedToUserAccount(): void
    {
        $reservationId = $this->reservationRepository->findOneBy(['reference' => 'ref1'])->getId();
        $user = $this->userRepository->findOneBy(['email' => 'user10@example.com']);
        $this->client->loginUser($user, 'api');
        $responseData = $this->getFailureResponseData($this->client, 'GET', "/api/users/me/reservations/$reservationId", expectedCode: 404);
        $this->assertEquals('Reservation not found', $responseData['message']);
    }

    #[Fixtures([UserReservationFixtures::class])]
    #[DataProviderExternal(UserReservationListDataProvider::class, 'listDataCases')]
    public function testList(int $page, int $perPage, int $total): void
    {
        $path = '/api/users/me/reservations?' . http_build_query([
            'page' => $page,
            'per_page' => $perPage,
        ]);
        $user = $this->userRepository->findOneBy(['email' => 'user1@example.com']);
        $this->client->loginUser($user, 'api');
        $responseData = $this->getSuccessfulResponseData($this->client, 'GET', $path);

        $offset = ($page - 1) * $perPage;
        $items = $this->reservationRepository->findBy([], ['id' => 'ASC'], $perPage, $offset);
        $formattedItems = $this->normalize($items, ReservationNormalizerGroup::USER_RESERVATIONS->normalizationGroups());

        $this->assertPaginatorResponse($responseData, $page, $perPage, $total, $formattedItems);
    }

    #[Fixtures([UserReservationSortingFixtures::class])]
    #[DataProviderExternal(UserReservationListDataProvider::class, 'filtersDataCases')]
    public function testListFilters(array $filters, array $expectedItemData): void
    {
        $mappedFilters = [];
        foreach($filters as $paramName => $value){
            $mappedFilters[$paramName] = match($paramName){
                'organization_id' => [$this->organizationRepository->findOneBy(['name' => $value])->getId()],
                'schedule_id' => [$this->scheduleRepository->findOneBy(['name' => $value])->getId()],
                'service_id' => [$this->serviceRepository->findOneBy(['name' => $value])->getId()],
                default => $value
            };
        }

        $path = '/api/users/me/reservations?' . http_build_query(['filters' => $mappedFilters]);
        $user = $this->userRepository->findOneBy(['email' => 'user1@example.com']);
        $this->client->loginUser($user, 'api');
        $responseData = $this->getSuccessfulResponseData($this->client, 'GET', $path);

        $this->assertCount(1, $responseData['items']);
        $dotExpectedItemData = array_dot($expectedItemData);
        $dotResponseItemData = array_dot($responseData['items'][0]);
        $this->assertArrayIsEqualToArrayOnlyConsideringListOfKeys($dotExpectedItemData, $dotResponseItemData, array_keys($dotExpectedItemData));
    }

    #[Fixtures([UserReservationSortingFixtures::class])]
    #[DataProviderExternal(UserReservationListDataProvider::class, 'sortingDataCases')]
    public function testListSorting(string $sorting, array $orderedItems): void
    {
        $path = '/api/users/me/reservations?' . http_build_query(['order' => $sorting]);
        $user = $this->userRepository->findOneBy(['email' => 'user1@example.com']);
        $this->client->loginUser($user, 'api');
        $responseData = $this->getSuccessfulResponseData($this->client, 'GET', $path);

        $this->assertGreaterThanOrEqual(count($orderedItems), count($responseData['items']));
        foreach($orderedItems as $indx => $item){
            $this->assertArrayIsEqualToArrayOnlyConsideringListOfKeys($item, $responseData['items'][$indx], array_keys($item));
        }
    }

    #[Fixtures([UserReservationSortingFixtures::class])]
    #[DataProviderExternal(UserReservationListDataProvider::class, 'validationDataCases')]
    public function testListValidation(array $params, array $expectedErrors): void
    {
        $user = $this->userRepository->findOneBy(['email' => 'user1@example.com']);
        $this->client->loginUser($user, 'api');
        $path = '/api/users/me/reservations?' . http_build_query($params);
        $this->assertPathValidation($this->client, 'GET', $path, [], $expectedErrors);
    }

    #[DataProviderExternal(UserReservationNotFoundDataProvider::class, 'dataCases')]
    public function testNotFoundResponses(string $path, string $method): void
    {
        $this->client->loginUser($this->user, 'api');
        $responseData = $this->getFailureResponseData($this->client, $method, $path, expectedCode: 404);
        $this->assertEquals('Reservation not found', $responseData['message']);
    }

    #[Fixtures([UserReservationFixtures::class])]
    #[DataProviderExternal(UserReservationAuthDataProvider::class, 'protectedPaths')]
    public function testAuthRequirementForProtectedPaths(string $path, string $method): void
    {
        $reservationId = $this->reservationRepository->findOneBy([])->getId();
        $path = str_replace('{reservation}', (string)$reservationId, $path);
        $this->assertPathIsProtected($path, $method);
    }
}
