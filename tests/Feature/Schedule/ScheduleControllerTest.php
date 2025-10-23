<?php

declare(strict_types=1);

namespace App\Tests\Feature\Schedule;

use App\DataFixtures\Test\OrganizationMember\OrganizationAdminFixtures;
use App\DataFixtures\Test\OrganizationMember\OrganizationMemberFixtures;
use App\DataFixtures\Test\Schedule\ScheduleFixtures;
use App\DataFixtures\Test\ScheduleService\ScheduleServiceFixtures;
use App\DataFixtures\Test\Schedule\ScheduleSortingFixtures;
use App\DataFixtures\Test\ScheduleService\ScheduleServiceSortingFixtures;
use App\DataFixtures\Test\User\UserFixtures;
use App\Enum\BlameableColumns;
use App\Enum\Schedule\ScheduleNormalizerGroup;
use App\Enum\TimestampsColumns;
use App\Repository\OrganizationMemberRepository;
use App\Repository\OrganizationRepository;
use App\Repository\ScheduleRepository;
use App\Repository\ServiceRepository;
use App\Repository\UserRepository;
use App\Response\ForbiddenResponse;
use App\Tests\Utils\Attribute\Fixtures;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use App\Tests\Utils\BaseWebTestCase;
use App\Tests\Feature\Schedule\DataProvider\ScheduleAuthDataProvider;
use App\Tests\Feature\Schedule\DataProvider\ScheduleCreateDataProvider;
use App\Tests\Feature\Schedule\DataProvider\ScheduleListDataProvider;
use App\Tests\Feature\Schedule\DataProvider\ScheduleNotFoundDataProvider;
use App\Tests\Feature\Schedule\DataProvider\SchedulePatchDataProvider;

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

    #[Fixtures([UserFixtures::class, OrganizationMemberFixtures::class, ScheduleServiceFixtures::class])]
    #[DataProviderExternal(ScheduleAuthDataProvider::class, 'privilegesOnlyPaths')]
    public function testPrivilegesRequirementForProtectedPaths(string $path, string $method, string $userEmail, array $parameters = []): void
    {
        $schedule = $this->scheduleRepository->findOneBy([]);
        $service = $this->serviceRepository->findOneBy([]);
        $path = str_replace('{schedule}', (string)($schedule->getId()), $path);
        $path = str_replace('{service}', (string)($service->getId()), $path);
        $user = $this->userRepository->findOneBy(['email' => $userEmail]);
        $organization = $schedule->getOrganization();
        $parameters = array_map(fn($val) => $val == '{organization}' ? $organization->getId() : $val, $parameters);

        $this->client->loginUser($user, 'api');
        $responseData = $this->getFailureResponseData($this->client, $method, $path, $parameters, expectedCode: 403);
        $this->assertEquals(ForbiddenResponse::RESPONSE_MESSAGE, $responseData['message']);
    }
}
