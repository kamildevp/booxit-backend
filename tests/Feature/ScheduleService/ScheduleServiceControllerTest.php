<?php

declare(strict_types=1);

namespace App\Tests\Feature\ScheduleService;

use App\DataFixtures\Test\Availability\AvailabilityFixtures;
use App\DataFixtures\Test\OrganizationMember\OrganizationMemberFixtures;
use App\DataFixtures\Test\Schedule\ScheduleFixtures;
use App\DataFixtures\Test\ScheduleService\ScheduleServiceFixtures;
use App\DataFixtures\Test\ScheduleService\ScheduleServiceAddValidationFixtures;
use App\DataFixtures\Test\ScheduleService\ScheduleServiceSortingFixtures;
use App\DataFixtures\Test\Service\ServiceFixtures;
use App\DataFixtures\Test\User\UserFixtures;
use App\Enum\Service\ServiceNormalizerGroup;
use App\Repository\OrganizationMemberRepository;
use App\Repository\OrganizationRepository;
use App\Repository\ScheduleRepository;
use App\Repository\ServiceRepository;
use App\Repository\UserRepository;
use App\Response\ForbiddenResponse;
use App\Tests\Utils\Attribute\Fixtures;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use App\Tests\Utils\BaseWebTestCase;
use App\Tests\Feature\ScheduleService\DataProvider\ScheduleServiceAddDataProvider as DataProviderScheduleServiceAddDataProvider;
use App\Tests\Feature\ScheduleService\DataProvider\ScheduleServiceAuthDataProvider;
use App\Tests\Feature\ScheduleService\DataProvider\ScheduleServiceGetAvailabilityDataProvider;
use App\Tests\Feature\ScheduleService\DataProvider\ScheduleServiceNotFoundDataProvider;
use App\Tests\Feature\Service\DataProvider\ServiceListDataProvider;

class ScheduleServiceControllerTest extends BaseWebTestCase
{
    protected ScheduleRepository $scheduleRepository;
    protected OrganizationRepository $organizationRepository;
    protected UserRepository $userRepository;
    protected OrganizationMemberRepository $organizationMemberRepository;
    protected ServiceRepository $serviceRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->organizationRepository = $this->container->get(OrganizationRepository::class);
        $this->scheduleRepository = $this->container->get(ScheduleRepository::class);
        $this->userRepository = $this->container->get(UserRepository::class);
        $this->organizationMemberRepository = $this->container->get(OrganizationMemberRepository::class);
        $this->serviceRepository = $this->container->get(ServiceRepository::class);
    }

    #[Fixtures([ScheduleFixtures::class, ServiceFixtures::class])]
    public function testAddService(): void
    {
        $this->client->loginUser($this->user, 'api');
        $schedule = $this->scheduleRepository->findOneBy([]);
        $service = $this->serviceRepository->findOneBy([]);
        $params = ['service_id' => $service->getId()];
        
        $responseData = $this->getSuccessfulResponseData($this->client,'POST', '/api/schedules/'.$schedule->getId().'/services', $params);
        $this->assertEquals(['message' => 'Service has been added to the schedule'], $responseData);
    }

    #[Fixtures([ScheduleFixtures::class, ServiceFixtures::class])]
    #[DataProviderExternal(DataProviderScheduleServiceAddDataProvider::class, 'validationDataCases')]
    public function testAddServiceValidation(array $params, array $expectedErrors): void
    {
        $schedule = $this->scheduleRepository->findOneBy([]);

        $this->client->loginUser($this->user, 'api');
        $this->assertPathValidation($this->client, 'POST', '/api/schedules/'.$schedule->getId().'/services', $params, $expectedErrors);
    }

    #[Fixtures([ScheduleFixtures::class, ServiceFixtures::class, ScheduleServiceAddValidationFixtures::class])]
    public function testAddServiceValidationResponseForInvalidService(): void
    {
        $schedule = $this->scheduleRepository->findOneBy([]); 
        $secondOrganization = $this->organizationRepository->findOneBy(['name' => ScheduleServiceAddValidationFixtures::ORGANIZATION_NAME]);
        $invalidService = $this->serviceRepository->findOneBy(['organization' => $secondOrganization]);
        $params = ['service_id' => $invalidService->getId()];
        $expectedErrors = ['service_id' => ['Service does not exist']];

        $this->client->loginUser($this->user, 'api');
        $this->assertPathValidation($this->client, 'POST', '/api/schedules/'.$schedule->getId().'/services', $params, $expectedErrors);
    }

    #[Fixtures([ScheduleServiceFixtures::class])]
    public function testAddServiceConflictResponseForAssignedService(): void
    {
        $schedule = $this->scheduleRepository->findOneBy([]); 
        $assignedService = $this->serviceRepository->findOneBy([]);
        $params = ['service_id' => $assignedService->getId()];

        $this->client->loginUser($this->user, 'api');
        $responseData = $this->getFailureResponseData($this->client, 'POST', '/api/schedules/'.$schedule->getId().'/services', $params, expectedCode: 409);
        $this->assertEquals('This service is already assigned to this schedule.', $responseData['message']);
    }

    #[Fixtures([ScheduleServiceFixtures::class])]
    public function testRemoveService(): void
    {
        $this->client->loginUser($this->user, 'api');
        $scheduleId = $this->scheduleRepository->findOneBy([])->getId();
        $serviceId = $this->serviceRepository->findOneBy([])->getId();
        
        $responseData = $this->getSuccessfulResponseData($this->client, 'DELETE', "/api/schedules/$scheduleId/services/$serviceId");
        $this->assertEquals(['message' => 'Service has been removed from schedule'], $responseData);
    }

    #[Fixtures([ScheduleFixtures::class, ServiceFixtures::class])]
    public function testRemoveNotFoundResponseForNotAssignedService(): void
    {
        $scheduleId = $this->scheduleRepository->findOneBy([])->getId(); 
        $serviceId = $this->serviceRepository->findOneBy([])->getId();

        $this->client->loginUser($this->user, 'api');
        $responseData = $this->getFailureResponseData($this->client, 'DELETE', "/api/schedules/$scheduleId/services/$serviceId", expectedCode: 404);
        $this->assertEquals('Service not found', $responseData['message']);
    }

    #[Fixtures([ScheduleServiceFixtures::class])]
    #[DataProviderExternal(ServiceListDataProvider::class, 'listDataCases')]
    public function testListServices(int $page, int $perPage, int $total): void
    {
        $schedule = $this->scheduleRepository->findOneBy([]);
        $path = '/api/schedules/'.$schedule->getId().'/services?' . http_build_query([
            'page' => $page,
            'per_page' => $perPage,
        ]);
        $this->client->loginUser($this->user, 'api');
        $responseData = $this->getSuccessfulResponseData($this->client, 'GET', $path);

        $offset = ($page - 1) * $perPage;
        $items = $this->serviceRepository->findBy([], ['id' => 'ASC'], $perPage, $offset);
        $formattedItems = $this->normalize($items, ServiceNormalizerGroup::ORGANIZATION_SERVICES->normalizationGroups());

        $this->assertPaginatorResponse($responseData, $page, $perPage, $total, $formattedItems);
    }

    #[Fixtures([ScheduleServiceSortingFixtures::class])]
    #[DataProviderExternal(ServiceListDataProvider::class, 'filtersDataCases')]
    public function testListServicesFilters(array $filters, array $expectedItemData): void
    {
        $schedule = $this->scheduleRepository->findOneBy([]);
        $path = '/api/schedules/'.$schedule->getId().'/services?' . http_build_query(['filters' => $filters]);
        $this->client->loginUser($this->user, 'api');
        $responseData = $this->getSuccessfulResponseData($this->client, 'GET', $path);

        $this->assertCount(1, $responseData['items']);
        $dotExpectedItemData = array_dot($expectedItemData);
        $dotResponseItemData = array_dot($responseData['items'][0]);
        $this->assertArrayIsEqualToArrayOnlyConsideringListOfKeys($dotExpectedItemData, $dotResponseItemData, array_keys($dotExpectedItemData));
    }

    #[Fixtures([ScheduleServiceSortingFixtures::class])]
    #[DataProviderExternal(ServiceListDataProvider::class, 'sortingDataCases')]
    public function testListServicesSorting(string $sorting, array $orderedItems): void
    {
        $schedule = $this->scheduleRepository->findOneBy([]);
        $path = '/api/schedules/'.$schedule->getId().'/services?'. http_build_query(['order' => $sorting]);
        $this->client->loginUser($this->user, 'api');
        $responseData = $this->getSuccessfulResponseData($this->client, 'GET', $path);

        $this->assertGreaterThanOrEqual(count($orderedItems), count($responseData['items']));
        foreach($orderedItems as $indx => $item){
            $dotExpectedItemData = array_dot($item);
            $dotResponseItemData = array_dot($responseData['items'][$indx]);
            $this->assertArrayIsEqualToArrayOnlyConsideringListOfKeys($dotExpectedItemData, $dotResponseItemData, array_keys($dotExpectedItemData));
        }
    }

    #[Fixtures([ScheduleServiceSortingFixtures::class])]
    #[DataProviderExternal(ServiceListDataProvider::class, 'validationDataCases')]
    public function testListServicesValidation(array $params, array $expectedErrors): void
    {
        $schedule = $this->scheduleRepository->findOneBy([]);
        $path = '/api/schedules/'.$schedule->getId().'/services?' . http_build_query($params);
        $this->client->loginUser($this->user, 'api');
        $this->assertPathValidation($this->client, 'GET', $path, [], $expectedErrors);
    }

    #[Fixtures([AvailabilityFixtures::class])]
    #[DataProviderExternal(ScheduleServiceGetAvailabilityDataProvider::class, 'validDataCases')]
    public function testGetScheduleServiceAvailability(array $query, string $serviceName, array $expectedData): void
    {
        $scheduleId = $this->scheduleRepository->findOneBy([])->getId();
        $serviceId = $this->serviceRepository->findOneBy(['name' => $serviceName])->getId();
        
        $path = "/api/schedules/$scheduleId/services/$serviceId/availability?" . http_build_query($query);
        $responseData = $this->getSuccessfulResponseData($this->client, 'GET', $path);
        $this->assertEquals($expectedData, $responseData);
    }

    #[Fixtures([AvailabilityFixtures::class])]
    #[DataProviderExternal(ScheduleServiceGetAvailabilityDataProvider::class, 'validationDataCases')]
    public function testGetScheduleServiceAvailabilityValidation(array $query, array $expectedErrors): void
    {
        $scheduleId = $this->scheduleRepository->findOneBy([])->getId();
        $serviceId = $this->serviceRepository->findOneBy([])->getId();
        
        $path = "/api/schedules/$scheduleId/services/$serviceId/availability?" . http_build_query($query);
        $this->client->loginUser($this->user, 'api');
        $this->assertPathValidation($this->client, 'GET', $path, [], $expectedErrors);
    }

    #[Fixtures([ScheduleServiceSortingFixtures::class])]
    #[DataProviderExternal(ScheduleServiceNotFoundDataProvider::class, 'dataCases')]
    public function testNotFoundResponses(string $path, string $method, string $expectedMessage): void
    {
        $schedule = $this->scheduleRepository->findOneBy([]);
        $path = str_replace('{schedule}', (string)($schedule->getId()), $path);

        $this->client->loginUser($this->user, 'api');
        $responseData = $this->getFailureResponseData($this->client, $method, $path, expectedCode: 404);
        $this->assertEquals($expectedMessage, $responseData['message']);
    }

    #[Fixtures([ScheduleServiceFixtures::class])]
    #[DataProviderExternal(ScheduleServiceAuthDataProvider::class, 'protectedPaths')]
    public function testAuthRequirementForProtectedPaths(string $path, string $method): void
    {
        $schedule = $this->scheduleRepository->findOneBy([]);
        $service = $this->serviceRepository->findOneBy([]);
        $path = str_replace('{schedule}', (string)($schedule->getId()), $path);
        $path = str_replace('{service}', (string)($service->getId()), $path);

        $this->assertPathIsProtected($path, $method);
    }

    #[Fixtures([UserFixtures::class, OrganizationMemberFixtures::class, ScheduleServiceFixtures::class])]
    #[DataProviderExternal(ScheduleServiceAuthDataProvider::class, 'privilegesOnlyPaths')]
    public function testPrivilegesRequirementForProtectedPaths(string $path, string $method, string $userEmail): void
    {
        $schedule = $this->scheduleRepository->findOneBy([]);
        $service = $this->serviceRepository->findOneBy([]);
        $path = str_replace('{schedule}', (string)($schedule->getId()), $path);
        $path = str_replace('{service}', (string)($service->getId()), $path);
        $user = $this->userRepository->findOneBy(['email' => $userEmail]);

        $this->client->loginUser($user, 'api');
        $responseData = $this->getFailureResponseData($this->client, $method, $path, [], expectedCode: 403);
        $this->assertEquals(ForbiddenResponse::RESPONSE_MESSAGE, $responseData['message']);
    }
}
