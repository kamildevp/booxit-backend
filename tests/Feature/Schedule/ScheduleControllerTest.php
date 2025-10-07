<?php

declare(strict_types=1);

namespace App\Tests\Feature\Schedule;

use App\DataFixtures\Test\OrganizationMember\OrganizationAdminFixtures;
use App\DataFixtures\Test\Schedule\ScheduleFixtures;
use App\DataFixtures\Test\Schedule\ScheduleServiceFixtures;
use App\DataFixtures\Test\Schedule\ScheduleSortingFixtures;
use App\DataFixtures\Test\Schedule\ScheduleServiceAddConflictFixtures;
use App\DataFixtures\Test\Schedule\ScheduleServiceSortingFixtures;
use App\DataFixtures\Test\Service\ServiceFixtures;
use App\DataFixtures\Test\User\UserFixtures;
use App\Entity\OrganizationMember;
use App\Enum\BlameableColumns;
use App\Enum\Schedule\ScheduleNormalizerGroup;
use App\Enum\Service\ServiceNormalizerGroup;
use App\Enum\TimestampsColumns;
use App\Repository\OrganizationMemberRepository;
use App\Repository\OrganizationRepository;
use App\Repository\ScheduleRepository;
use App\Repository\ServiceRepository;
use App\Repository\UserRepository;
use App\Response\ForbiddenResponse;
use App\Service\Entity\ScheduleService;
use App\Tests\Utils\Attribute\Fixtures;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use App\Tests\Utils\BaseWebTestCase;
use App\Tests\Feature\Schedule\DataProvider\ScheduleAuthDataProvider;
use App\Tests\Feature\Schedule\DataProvider\ScheduleCreateDataProvider;
use App\Tests\Feature\Schedule\DataProvider\ScheduleListDataProvider;
use App\Tests\Feature\Schedule\DataProvider\ScheduleNotFoundDataProvider;
use App\Tests\Feature\Schedule\DataProvider\SchedulePatchDataProvider;
use App\Tests\Feature\Schedule\DataProvider\ScheduleServiceAddDataProvider;
use App\Tests\Feature\Service\DataProvider\ServiceListDataProvider;

class ScheduleControllerTest extends BaseWebTestCase
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

    #[Fixtures([OrganizationAdminFixtures::class])]
    #[DataProviderExternal(ScheduleCreateDataProvider::class, 'validDataCases')]
    public function testCreate(array $params, array $expectedResponseData): void
    {
        $organizationId = $this->organizationRepository->findOneBy([])->getId();
        $params['organization_id'] = $organizationId;
        $expectedResponseData['organization']['id'] = $organizationId;
        $this->client->loginUser($this->user, 'api');
        $responseData = $this->getSuccessfulResponseData($this->client,'POST', '/api/schedules', $params);
        $this->assertIsInt($responseData['id']);
        $this->assertArrayIsEqualToArrayOnlyConsideringListOfKeys($expectedResponseData, $responseData, array_keys($expectedResponseData));
    }

    #[Fixtures([OrganizationAdminFixtures::class])]
    #[DataProviderExternal(ScheduleCreateDataProvider::class, 'validationDataCases')]
    public function testCreateValidation(array $params, array $expectedErrors): void
    {
        $organizationId = $this->organizationRepository->findOneBy([])->getId();
        $params['organization_id'] = $organizationId;
        $this->client->loginUser($this->user, 'api');
        $this->assertPathValidation($this->client, 'POST', '/api/schedules', $params, $expectedErrors);
    }

    #[Fixtures([ScheduleFixtures::class])]
    public function testGet(): void
    {
        $schedule = $this->scheduleRepository->findOneBy(['name' => 'Test Schedule 1']);
        $expectedResponseData = $this->normalize($schedule, ScheduleNormalizerGroup::PUBLIC->normalizationGroups());
        $scheduleId = $schedule->getId();
        $responseData = $this->getSuccessfulResponseData($this->client, 'GET', "/api/schedules/$scheduleId");

        $this->assertEquals($expectedResponseData, $responseData);
    }

    #[Fixtures([ScheduleFixtures::class])]
    #[DataProviderExternal(SchedulePatchDataProvider::class, 'validDataCases')]
    public function testPatch(array $params, array $expectedFieldValues): void
    {
        $schedule = $this->scheduleRepository->findOneBy(['name' => 'Test Schedule 1']);
        $normalizedSchedule = $this->normalize($schedule, ScheduleNormalizerGroup::PRIVATE->normalizationGroups());
        $expectedResponseData = array_merge($normalizedSchedule, $expectedFieldValues);
        $this->client->loginUser($this->user, 'api');
        $responseData = $this->getSuccessfulResponseData($this->client, 'PATCH', '/api/schedules/'.$schedule->getId(), $params);

        $this->assertArrayIsEqualToArrayIgnoringListOfKeys($expectedResponseData, $responseData, [TimestampsColumns::UPDATED_AT->value, BlameableColumns::UPDATED_BY->value]);
    }

    #[Fixtures([ScheduleFixtures::class])]
    #[DataProviderExternal(SchedulePatchDataProvider::class, 'validationDataCases')]
    public function testPatchValidation(array $params, array $expectedErrors): void
    {
        $schedule = $this->scheduleRepository->findOneBy(['name' => 'Test Schedule 1']);
        $this->client->loginUser($this->user, 'api');
        $this->assertPathValidation($this->client, 'PATCH', '/api/schedules/'.$schedule->getId(), $params, $expectedErrors);
    }

    #[Fixtures([ScheduleFixtures::class])]
    public function testDelete(): void
    {
        $schedule = $this->scheduleRepository->findOneBy(['name' => 'Test Schedule 1']);
        $this->client->loginUser($this->user, 'api');
        $responseData = $this->getSuccessfulResponseData($this->client, 'DELETE', '/api/schedules/'.$schedule->getId());

        $this->assertEquals('Schedule removed successfully', $responseData['message']);
    }

    #[Fixtures([ScheduleFixtures::class])]
    #[DataProviderExternal(ScheduleListDataProvider::class, 'listDataCases')]
    public function testList(int $page, int $perPage, int $total): void
    {
        $path = '/api/schedules?' . http_build_query([
            'page' => $page,
            'per_page' => $perPage,
        ]);
        $responseData = $this->getSuccessfulResponseData($this->client, 'GET', $path);

        $offset = ($page - 1) * $perPage;
        $items = $this->scheduleRepository->findBy([], ['id' => 'ASC'], $perPage, $offset);
        $formattedItems = $this->normalize($items, ScheduleNormalizerGroup::PUBLIC->normalizationGroups());

        $this->assertPaginatorResponse($responseData, $page, $perPage, $total, $formattedItems);
    }

    #[Fixtures([ScheduleSortingFixtures::class])]
    #[DataProviderExternal(ScheduleListDataProvider::class, 'filtersDataCases')]
    public function testListFilters(array $filters, array $expectedItemData): void
    {
        $path = '/api/schedules?' . http_build_query(['filters' => $filters]);
        $responseData = $this->getSuccessfulResponseData($this->client, 'GET', $path);

        $this->assertCount(1, $responseData['items']);
        $this->assertArrayIsEqualToArrayOnlyConsideringListOfKeys($expectedItemData, $responseData['items'][0], array_keys($expectedItemData));
    }

    #[Fixtures([ScheduleSortingFixtures::class])]
    #[DataProviderExternal(ScheduleListDataProvider::class, 'sortingDataCases')]
    public function testListSorting(string $sorting, array $orderedItems): void
    {
        $path = '/api/schedules?' . http_build_query(['order' => $sorting]);
        $responseData = $this->getSuccessfulResponseData($this->client, 'GET', $path);

        $this->assertGreaterThanOrEqual(count($orderedItems), count($responseData['items']));
        foreach($orderedItems as $indx => $item){
            $this->assertArrayIsEqualToArrayOnlyConsideringListOfKeys($item, $responseData['items'][$indx], array_keys($item));
        }
    }

    #[DataProviderExternal(ScheduleListDataProvider::class, 'validationDataCases')]
    public function testListValidation(array $params, array $expectedErrors): void
    {
        $path = '/api/schedules?' . http_build_query($params);
        $this->assertPathValidation($this->client, 'GET', $path, [], $expectedErrors);
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
    #[DataProviderExternal(ScheduleServiceAddDataProvider::class, 'validationDataCases')]
    public function testAddServiceValidation(array $params, array $expectedErrors): void
    {
        $schedule = $this->scheduleRepository->findOneBy([]);

        $this->client->loginUser($this->user, 'api');
        $this->assertPathValidation($this->client, 'POST', '/api/schedules/'.$schedule->getId().'/services', $params, $expectedErrors);
    }

    #[Fixtures([ScheduleFixtures::class, ServiceFixtures::class, ScheduleServiceAddConflictFixtures::class])]
    public function testAddServiceConflictResponseForInvalidService(): void
    {
        $schedule = $this->scheduleRepository->findOneBy([]); 
        $secondOrganization = $this->organizationRepository->findOneBy(['name' => ScheduleServiceAddConflictFixtures::ORGANIZATION_NAME]);
        $invalidService = $this->serviceRepository->findOneBy(['organization' => $secondOrganization]);
        $params = ['service_id' => $invalidService->getId()];

        $this->client->loginUser($this->user, 'api');
        $responseData = $this->getFailureResponseData($this->client, 'POST', '/api/schedules/'.$schedule->getId().'/services', $params, expectedCode: 409);
        $this->assertEquals('This service belongs to different organization.', $responseData['message']);
    }

    #[Fixtures([ScheduleServiceFixtures::class])]
    public function testCreateConflictResponseForAssignedService(): void
    {
        $schedule = $this->scheduleRepository->findOneBy([]); 
        $assignedService = $this->serviceRepository->findOneBy([]);
        $params = ['service_id' => $assignedService->getId()];

        $this->client->loginUser($this->user, 'api');
        $responseData = $this->getFailureResponseData($this->client, 'POST', '/api/schedules/'.$schedule->getId().'/services', $params, expectedCode: 409);
        $this->assertEquals('This service is already assigned to this schedule.', $responseData['message']);
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

    #[Fixtures([ScheduleServiceSortingFixtures::class])]
    #[DataProviderExternal(ScheduleNotFoundDataProvider::class, 'dataCases')]
    public function testNotFoundResponses(string $path, string $method, string $expectedMessage): void
    {
        $schedule = $this->scheduleRepository->findOneBy([]);
        $path = str_replace('{schedule}', (string)($schedule->getId()), $path);

        $this->client->loginUser($this->user, 'api');
        $responseData = $this->getFailureResponseData($this->client, $method, $path, expectedCode: 404);
        $this->assertEquals($expectedMessage, $responseData['message']);
    }

    #[Fixtures([ScheduleServiceFixtures::class])]
    #[DataProviderExternal(ScheduleAuthDataProvider::class, 'protectedPaths')]
    public function testAuthRequirementForProtectedPaths(string $path, string $method): void
    {
        $schedule = $this->scheduleRepository->findOneBy([]);
        $service = $this->serviceRepository->findOneBy([]);
        $path = str_replace('{schedule}', (string)($schedule->getId()), $path);
        $path = str_replace('{service}', (string)($service->getId()), $path);

        $this->assertPathIsProtected($path, $method);
    }

    #[Fixtures([UserFixtures::class, ScheduleServiceFixtures::class])]
    #[DataProviderExternal(ScheduleAuthDataProvider::class, 'scheduleManagementPrivilegesOnlyPaths')]
    public function testScheduleManagementPrivilegesRequirementForProtectedPaths(string $path, string $method, ?string $role, array $parameters = []): void
    {
        $schedule = $this->scheduleRepository->findOneBy([]);
        $service = $this->serviceRepository->findOneBy([]);
        $path = str_replace('{schedule}', (string)($schedule->getId()), $path);
        $path = str_replace('{service}', (string)($service->getId()), $path);
        $user = $this->userRepository->findOneBy(['email' => 'user1@example.com']);
        $organization = $schedule->getOrganization();
        $parameters = array_map(fn($val) => $val == '{organization}' ? $organization->getId() : $val, $parameters);
        if(!empty($role)){
            $organizationMember = new OrganizationMember();
            $organizationMember->setOrganization($organization);
            $organizationMember->setAppUser($user);
            $organizationMember->setRole($role);
            $this->organizationMemberRepository->save($organizationMember, true);
        }

        $this->client->loginUser($user, 'api');
        $responseData = $this->getFailureResponseData($this->client, $method, $path, $parameters, expectedCode: 403);
        $this->assertEquals(ForbiddenResponse::RESPONSE_MESSAGE, $responseData['message']);
    }
}
