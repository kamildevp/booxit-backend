<?php

declare(strict_types=1);

namespace App\Tests\Feature\Organization;

use App\DataFixtures\Test\Organization\OrganizationBannerFixtures;
use App\DataFixtures\Test\Organization\OrganizationFixtures;
use App\DataFixtures\Test\Organization\OrganizationSortingFixtures;
use App\DataFixtures\Test\User\UserFixtures;
use App\Entity\OrganizationMember;
use App\Enum\BlameableColumns;
use App\Enum\File\UploadType;
use App\Enum\Organization\OrganizationNormalizerGroup;
use App\Enum\TimestampsColumns;
use App\Repository\OrganizationMemberRepository;
use App\Repository\OrganizationRepository;
use App\Repository\UserRepository;
use App\Response\ForbiddenResponse;
use App\Tests\Utils\Attribute\Fixtures;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use App\Tests\Utils\BaseWebTestCase;
use App\Tests\Feature\Organization\DataProvider\OrganizationAuthDataProvider;
use App\Tests\Feature\Organization\DataProvider\OrganizationCreateDataProvider;
use App\Tests\Feature\Organization\DataProvider\OrganizationListDataProvider;
use App\Tests\Feature\Organization\DataProvider\OrganizationNotFoundDataProvider;
use App\Tests\Feature\Organization\DataProvider\OrganizationPatchDataProvider;
use App\Tests\Feature\Organization\DataProvider\OrganizationUpdateBannerDataProvider;

class OrganizationControllerTest extends BaseWebTestCase
{
    protected OrganizationRepository $organizationRepository;
    protected OrganizationMemberRepository $organizationMemberRepository;
    protected UserRepository $userRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->organizationRepository = $this->container->get(OrganizationRepository::class);
        $this->organizationMemberRepository = $this->container->get(OrganizationMemberRepository::class);
        $this->userRepository = $this->container->get(UserRepository::class);
    }

    #[DataProviderExternal(OrganizationCreateDataProvider::class, 'validDataCases')]
    public function testCreate(array $params, array $expectedResponseData): void
    {
        $this->client->loginUser($this->user, 'api');
        $responseData = $this->getSuccessfulResponseData($this->client,'POST', '/api/organizations', $params);
        $this->assertIsInt($responseData['id']);
        $this->assertArrayIsEqualToArrayOnlyConsideringListOfKeys($expectedResponseData, $responseData, array_keys($expectedResponseData));
    }

    #[Fixtures([OrganizationFixtures::class])]
    #[DataProviderExternal(OrganizationCreateDataProvider::class, 'validationDataCases')]
    public function testCreateValidation(array $params, array $expectedErrors): void
    {
        $this->client->loginUser($this->user, 'api');
        $this->assertPathValidation($this->client, 'POST', '/api/organizations', $params, $expectedErrors);
    }

    #[Fixtures([OrganizationFixtures::class])]
    public function testGet(): void
    {
        $organization = $this->organizationRepository->findOneBy(['name' => 'Test Organization 1']);
        $expectedResponseData = $this->normalize($organization, OrganizationNormalizerGroup::PUBLIC->normalizationGroups());
        $organizationId = $organization->getId();
        $responseData = $this->getSuccessfulResponseData($this->client, 'GET', "/api/organizations/$organizationId");

        $this->assertEquals($expectedResponseData, $responseData);
    }

    #[Fixtures([OrganizationFixtures::class])]
    #[DataProviderExternal(OrganizationPatchDataProvider::class, 'validDataCases')]
    public function testPatch(array $params, array $expectedFieldValues): void
    {
        $organization = $this->organizationRepository->findOneBy(['name' => 'Test Organization 1']);
        $normalizedOrganization = $this->normalize($organization, OrganizationNormalizerGroup::PRIVATE->normalizationGroups());
        $expectedResponseData = array_merge($normalizedOrganization, $expectedFieldValues);
        $this->client->loginUser($this->user, 'api');
        $responseData = $this->getSuccessfulResponseData($this->client, 'PATCH', '/api/organizations/'.$organization->getId(), $params);

        $this->assertArrayIsEqualToArrayIgnoringListOfKeys($expectedResponseData, $responseData, [TimestampsColumns::UPDATED_AT->value, BlameableColumns::UPDATED_BY->value]);
    }

    #[Fixtures([OrganizationFixtures::class])]
    #[DataProviderExternal(OrganizationPatchDataProvider::class, 'validationDataCases')]
    public function testPatchValidation(array $params, array $expectedErrors): void
    {
        $organization = $this->organizationRepository->findOneBy(['name' => 'Test Organization 1']);
        $this->client->loginUser($this->user, 'api');
        $this->assertPathValidation($this->client, 'PATCH', '/api/organizations/'.$organization->getId(), $params, $expectedErrors);
    }

    #[Fixtures([OrganizationFixtures::class])]
    public function testDelete(): void
    {
        $organization = $this->organizationRepository->findOneBy(['name' => 'Test Organization 1']);
        $this->client->loginUser($this->user, 'api');
        $responseData = $this->getSuccessfulResponseData($this->client, 'DELETE', '/api/organizations/'.$organization->getId());

        $this->assertEquals('Organization removed successfully', $responseData['message']);
    }

    #[Fixtures([OrganizationFixtures::class])]
    #[DataProviderExternal(OrganizationListDataProvider::class, 'listDataCases')]
    public function testList(int $page, int $perPage, int $total): void
    {
        $path = '/api/organizations?' . http_build_query([
            'page' => $page,
            'per_page' => $perPage,
        ]);
        $responseData = $this->getSuccessfulResponseData($this->client, 'GET', $path);

        $offset = ($page - 1) * $perPage;
        $items = $this->organizationRepository->findBy([], ['id' => 'ASC'], $perPage, $offset);
        $formattedItems = $this->normalize($items, OrganizationNormalizerGroup::PUBLIC->normalizationGroups());

        $this->assertPaginatorResponse($responseData, $page, $perPage, $total, $formattedItems);
    }

    #[Fixtures([OrganizationSortingFixtures::class])]
    #[DataProviderExternal(OrganizationListDataProvider::class, 'filtersDataCases')]
    public function testListFilters(array $filters, array $expectedItemData): void
    {
        $path = '/api/organizations?' . http_build_query(['filters' => $filters]);
        $responseData = $this->getSuccessfulResponseData($this->client, 'GET', $path);

        $this->assertCount(1, $responseData['items']);
        $this->assertArrayIsEqualToArrayOnlyConsideringListOfKeys($expectedItemData, $responseData['items'][0], array_keys($expectedItemData));
    }

    #[Fixtures([OrganizationSortingFixtures::class])]
    #[DataProviderExternal(OrganizationListDataProvider::class, 'sortingDataCases')]
    public function testListSorting(string $sorting, array $orderedItems): void
    {
        $path = '/api/organizations?' . http_build_query(['order' => $sorting]);
        $responseData = $this->getSuccessfulResponseData($this->client, 'GET', $path);

        $this->assertGreaterThanOrEqual(count($orderedItems), count($responseData['items']));
        foreach($orderedItems as $indx => $item){
            $this->assertArrayIsEqualToArrayOnlyConsideringListOfKeys($item, $responseData['items'][$indx], array_keys($item));
        }
    }

    #[DataProviderExternal(OrganizationListDataProvider::class, 'validationDataCases')]
    public function testListValidation(array $params, array $expectedErrors): void
    {
        $path = '/api/organizations?' . http_build_query($params);
        $this->assertPathValidation($this->client, 'GET', $path, [], $expectedErrors);
    }

    #[Fixtures([OrganizationFixtures::class])]
    #[DataProviderExternal(OrganizationUpdateBannerDataProvider::class, 'validDataCases')]
    public function testUpdateBanner(string $fileName, ?string $contentType): void
    {
        $this->createUploadStorageDir(UploadType::ORGANIZATION_BANNER);

        $filePath = $this->projectDir . '/src/DataFixtures/Test/FileUpload/'.$fileName;
        $organization = $this->organizationRepository->findOneBy(['name' => 'Test Organization 1']);
        $this->client->loginUser($this->user, 'api');

        $this->client->request(
            'PUT',
            '/api/organizations/'.$organization->getId().'/banner',
            server: $contentType ? ['CONTENT_TYPE' => $contentType] : [],
            content: file_get_contents($filePath)
        );

        $this->assertResponseIsSuccessful();
        $response = $this->getJsonResponse($this->client);
        $this->assertEquals('Organization banner updated successfully', $response['data']['message']);

        $this->clearStorage();
    }

    #[Fixtures([OrganizationFixtures::class])]
    #[DataProviderExternal(OrganizationUpdateBannerDataProvider::class, 'validationDataCases')]
    public function testUpdateBannerValidation(string $fileName, ?string $contentType, string $expectedMessage, int $expectedCode): void
    {
        $this->createUploadStorageDir(UploadType::ORGANIZATION_BANNER);

        $filePath = !empty($fileName) ? $this->projectDir . '/src/DataFixtures/Test/FileUpload/'.$fileName : null;
        $organization = $this->organizationRepository->findOneBy(['name' => 'Test Organization 1']);
        $this->client->loginUser($this->user, 'api');

        $this->client->request(
            'PUT',
            '/api/organizations/'.$organization->getId().'/banner',
            server: $contentType ? ['CONTENT_TYPE' => $contentType] : [],
            content: $filePath ? file_get_contents($filePath) : null
        );

        $response = $this->getJsonResponse($this->client);
        $this->assertResponseStatusCodeSame($expectedCode);
        $this->assertFailureResponse($response);
        $this->assertEquals($expectedMessage, $response['data']['message']);

        $this->clearStorage();
    }

    #[Fixtures([OrganizationBannerFixtures::class])]
    public function testGetBanner(): void
    {
        $organization = $this->organizationRepository->findOneBy(['name' => 'Test Organization']);
        $this->client->loginUser($this->user, 'api');
        $this->client->request('GET', '/api/organizations/'.$organization->getId().'/banner');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', OrganizationBannerFixtures::BANNER_FILE_MIME_TYPE);
    }

    #[Fixtures([OrganizationFixtures::class])]
    public function testGetBannerNotFound(): void
    {
        $organization = $this->organizationRepository->findOneBy(['name' => 'Test Organization 1']);
        $this->client->loginUser($this->user, 'api');
        $this->client->request('GET', '/api/organizations/'.$organization->getId().'/banner');

        $response = $this->getJsonResponse($this->client);
        $this->assertResponseStatusCodeSame(404);
        $this->assertFailureResponse($response);
        $this->assertEquals("Organization banner not found", $response['data']['message']);
    }

    #[DataProviderExternal(OrganizationNotFoundDataProvider::class, 'dataCases')]
    public function testNotFoundResponses(string $path, string $method): void
    {
        $this->client->loginUser($this->user, 'api');
        $responseData = $this->getFailureResponseData($this->client, $method, $path, expectedCode: 404);
        $this->assertEquals('Organization not found', $responseData['message']);
    }

    #[Fixtures([OrganizationFixtures::class])]
    #[DataProviderExternal(OrganizationAuthDataProvider::class, 'protectedPaths')]
    public function testAuthRequirementForProtectedPaths(string $path, string $method): void
    {
        $organization = $this->organizationRepository->findOneBy([]);
        $path = str_replace('{organization}', (string)($organization->getId()), $path);

        $this->assertPathIsProtected($path, $method);
    }

    #[Fixtures([UserFixtures::class, OrganizationFixtures::class])]
    #[DataProviderExternal(OrganizationAuthDataProvider::class, 'organizationAdminOnlyPaths')]
    public function testOrganizationAdminRoleRequirementForProtectedPaths(string $path, string $method, ?string $role): void
    {
        $organization = $this->organizationRepository->findOneBy([]);
        $path = str_replace('{organization}', (string)($organization->getId()), $path);
        $user = $this->userRepository->findOneBy(['email' => 'user1@example.com']);
        if(!empty($role)){
            $organizationMember = new OrganizationMember();
            $organizationMember->setOrganization($organization);
            $organizationMember->setAppUser($user);
            $organizationMember->setRole($role);
            $this->organizationMemberRepository->save($organizationMember, true);
        }

        $this->client->loginUser($user, 'api');
        $responseData = $this->getFailureResponseData($this->client, $method, $path, expectedCode: 403);
        $this->assertEquals(ForbiddenResponse::RESPONSE_MESSAGE, $responseData['message']);
    }
}
