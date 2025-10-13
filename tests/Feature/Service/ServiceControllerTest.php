<?php

declare(strict_types=1);

namespace App\Tests\Feature\Service;

use App\DataFixtures\Test\OrganizationMember\OrganizationAdminFixtures;
use App\DataFixtures\Test\OrganizationMember\OrganizationMemberFixtures;
use App\DataFixtures\Test\Service\ServiceFixtures;
use App\DataFixtures\Test\Service\ServiceSortingFixtures;
use App\DataFixtures\Test\User\UserFixtures;
use App\Entity\OrganizationMember;
use App\Enum\BlameableColumns;
use App\Enum\Service\ServiceNormalizerGroup;
use App\Enum\TimestampsColumns;
use App\Repository\OrganizationMemberRepository;
use App\Repository\OrganizationRepository;
use App\Repository\ServiceRepository;
use App\Repository\UserRepository;
use App\Response\ForbiddenResponse;
use App\Tests\Utils\Attribute\Fixtures;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use App\Tests\Utils\BaseWebTestCase;
use App\Tests\Feature\Service\DataProvider\ServiceAuthDataProvider;
use App\Tests\Feature\Service\DataProvider\ServiceCreateDataProvider;
use App\Tests\Feature\Service\DataProvider\ServiceListDataProvider;
use App\Tests\Feature\Service\DataProvider\ServiceNotFoundDataProvider;
use App\Tests\Feature\Service\DataProvider\ServicePatchDataProvider;

class ServiceControllerTest extends BaseWebTestCase
{
    protected ServiceRepository $serviceRepository;
    protected OrganizationRepository $organizationRepository;
    protected UserRepository $userRepository;
    protected OrganizationMemberRepository $organizationMemberRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->organizationRepository = $this->container->get(OrganizationRepository::class);
        $this->serviceRepository = $this->container->get(ServiceRepository::class);
        $this->userRepository = $this->container->get(UserRepository::class);
        $this->organizationMemberRepository = $this->container->get(OrganizationMemberRepository::class);
    }

    #[Fixtures([OrganizationAdminFixtures::class])]
    #[DataProviderExternal(ServiceCreateDataProvider::class, 'validDataCases')]
    public function testCreate(array $params, array $expectedResponseData): void
    {
        $organizationId = $this->organizationRepository->findOneBy([])->getId();
        $params['organization_id'] = $organizationId;
        $expectedResponseData['organization']['id'] = $organizationId;
        $this->client->loginUser($this->user, 'api');
        $responseData = $this->getSuccessfulResponseData($this->client,'POST', '/api/services', $params);
        $this->assertIsInt($responseData['id']);
        $this->assertArrayIsEqualToArrayOnlyConsideringListOfKeys($expectedResponseData, $responseData, array_keys($expectedResponseData));
    }

    #[Fixtures([OrganizationAdminFixtures::class])]
    #[DataProviderExternal(ServiceCreateDataProvider::class, 'validationDataCases')]
    public function testCreateValidation(array $params, array $expectedErrors): void
    {
        $organizationId = $this->organizationRepository->findOneBy([])->getId();
        $params['organization_id'] = $organizationId;
        $this->client->loginUser($this->user, 'api');
        $this->assertPathValidation($this->client, 'POST', '/api/services', $params, $expectedErrors);
    }

    #[Fixtures([ServiceFixtures::class])]
    public function testGet(): void
    {
        $service = $this->serviceRepository->findOneBy(['name' => 'Test Service 1']);
        $expectedResponseData = $this->normalize($service, ServiceNormalizerGroup::PUBLIC->normalizationGroups());
        $serviceId = $service->getId();
        $responseData = $this->getSuccessfulResponseData($this->client, 'GET', "/api/services/$serviceId");

        $this->assertEquals($expectedResponseData, $responseData);
    }

    #[Fixtures([ServiceFixtures::class])]
    #[DataProviderExternal(ServicePatchDataProvider::class, 'validDataCases')]
    public function testPatch(array $params, array $expectedFieldValues): void
    {
        $service = $this->serviceRepository->findOneBy(['name' => 'Test Service 1']);
        $normalizedService = $this->normalize($service, ServiceNormalizerGroup::PRIVATE->normalizationGroups());
        $expectedResponseData = array_merge($normalizedService, $expectedFieldValues);
        $this->client->loginUser($this->user, 'api');
        $responseData = $this->getSuccessfulResponseData($this->client, 'PATCH', '/api/services/'.$service->getId(), $params);

        $this->assertArrayIsEqualToArrayIgnoringListOfKeys($expectedResponseData, $responseData, [TimestampsColumns::UPDATED_AT->value, BlameableColumns::UPDATED_BY->value]);
    }

    #[Fixtures([ServiceFixtures::class])]
    #[DataProviderExternal(ServicePatchDataProvider::class, 'validationDataCases')]
    public function testPatchValidation(array $params, array $expectedErrors): void
    {
        $service = $this->serviceRepository->findOneBy(['name' => 'Test Service 1']);
        $this->client->loginUser($this->user, 'api');
        $this->assertPathValidation($this->client, 'PATCH', '/api/services/'.$service->getId(), $params, $expectedErrors);
    }

    #[Fixtures([ServiceFixtures::class])]
    public function testDelete(): void
    {
        $service = $this->serviceRepository->findOneBy(['name' => 'Test Service 1']);
        $this->client->loginUser($this->user, 'api');
        $responseData = $this->getSuccessfulResponseData($this->client, 'DELETE', '/api/services/'.$service->getId());

        $this->assertEquals('Service removed successfully', $responseData['message']);
    }

    #[Fixtures([ServiceFixtures::class])]
    #[DataProviderExternal(ServiceListDataProvider::class, 'listDataCases')]
    public function testList(int $page, int $perPage, int $total): void
    {
        $path = '/api/services?' . http_build_query([
            'page' => $page,
            'per_page' => $perPage,
        ]);
        $responseData = $this->getSuccessfulResponseData($this->client, 'GET', $path);

        $offset = ($page - 1) * $perPage;
        $items = $this->serviceRepository->findBy([], ['id' => 'ASC'], $perPage, $offset);
        $formattedItems = $this->normalize($items, ServiceNormalizerGroup::PUBLIC->normalizationGroups());

        $this->assertPaginatorResponse($responseData, $page, $perPage, $total, $formattedItems);
    }

    #[Fixtures([ServiceSortingFixtures::class])]
    #[DataProviderExternal(ServiceListDataProvider::class, 'filtersDataCases')]
    public function testListFilters(array $filters, array $expectedItemData): void
    {
        $path = '/api/services?' . http_build_query(['filters' => $filters]);
        $responseData = $this->getSuccessfulResponseData($this->client, 'GET', $path);

        $this->assertCount(1, $responseData['items']);
        $this->assertArrayIsEqualToArrayOnlyConsideringListOfKeys($expectedItemData, $responseData['items'][0], array_keys($expectedItemData));
    }

    #[Fixtures([ServiceSortingFixtures::class])]
    #[DataProviderExternal(ServiceListDataProvider::class, 'sortingDataCases')]
    public function testListSorting(string $sorting, array $orderedItems): void
    {
        $path = '/api/services?' . http_build_query(['order' => $sorting]);
        $responseData = $this->getSuccessfulResponseData($this->client, 'GET', $path);

        $this->assertGreaterThanOrEqual(count($orderedItems), count($responseData['items']));
        foreach($orderedItems as $indx => $item){
            $this->assertArrayIsEqualToArrayOnlyConsideringListOfKeys($item, $responseData['items'][$indx], array_keys($item));
        }
    }

    #[DataProviderExternal(ServiceListDataProvider::class, 'validationDataCases')]
    public function testListValidation(array $params, array $expectedErrors): void
    {
        $path = '/api/services?' . http_build_query($params);
        $this->assertPathValidation($this->client, 'GET', $path, [], $expectedErrors);
    }

    #[DataProviderExternal(ServiceNotFoundDataProvider::class, 'dataCases')]
    public function testNotFoundResponses(string $path, string $method): void
    {
        $this->client->loginUser($this->user, 'api');
        $responseData = $this->getFailureResponseData($this->client, $method, $path, expectedCode: 404);
        $this->assertEquals('Service not found', $responseData['message']);
    }

    #[Fixtures([ServiceSortingFixtures::class])]
    #[DataProviderExternal(ServiceAuthDataProvider::class, 'protectedPaths')]
    public function testAuthRequirementForProtectedPaths(string $path, string $method): void
    {
        $service = $this->serviceRepository->findOneBy([]);
        $path = str_replace('{service}', (string)($service->getId()), $path);

        $this->assertPathIsProtected($path, $method);
    }

    #[Fixtures([UserFixtures::class, OrganizationMemberFixtures::class, ServiceFixtures::class])]
    #[DataProviderExternal(ServiceAuthDataProvider::class, 'privilegesOnlyPaths')]
    public function testPrivilegesRequirementForProtectedPaths(string $path, string $method, string $userEmail, array $parameters = []): void
    {
        $service = $this->serviceRepository->findOneBy([]);
        $path = str_replace('{service}', (string)($service->getId()), $path);
        $user = $this->userRepository->findOneBy(['email' => $userEmail]);
        $organization = $service->getOrganization();
        $parameters = array_map(fn($val) => $val == '{organization}' ? $organization->getId() : $val, $parameters);

        $this->client->loginUser($user, 'api');
        $responseData = $this->getFailureResponseData($this->client, $method, $path, $parameters, expectedCode: 403);
        $this->assertEquals(ForbiddenResponse::RESPONSE_MESSAGE, $responseData['message']);
    }
}
