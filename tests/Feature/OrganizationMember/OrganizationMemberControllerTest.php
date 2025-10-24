<?php

declare(strict_types=1);

namespace App\Tests\Feature\OrganizationMember;

use App\DataFixtures\Test\Organization\OrganizationFixtures;
use App\DataFixtures\Test\OrganizationMember\OrganizationMemberFixtures;
use App\DataFixtures\Test\OrganizationMember\OrganizationMemberScheduleAssignmentsFixtures;
use App\DataFixtures\Test\OrganizationMember\OrganizationMemberScheduleAssignmentsSortingFixtures;
use App\DataFixtures\Test\OrganizationMember\OrganizationMemberSortingFixtures;
use App\DataFixtures\Test\User\UserFixtures;
use App\Entity\OrganizationMember;
use App\Enum\Organization\OrganizationRole;
use App\Enum\OrganizationMember\OrganizationMemberNormalizerGroup;
use App\Enum\ScheduleAssignment\ScheduleAssignmentNormalizerGroup;
use App\Enum\User\UserNormalizerGroup;
use App\Repository\OrganizationMemberRepository;
use App\Repository\OrganizationRepository;
use App\Repository\ScheduleAssignmentRepository;
use App\Repository\UserRepository;
use App\Response\ForbiddenResponse;
use App\Tests\Utils\Attribute\Fixtures;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use App\Tests\Utils\BaseWebTestCase;
use App\Tests\Feature\OrganizationMember\DataProvider\OrganizationMemberCreateDataProvider;
use App\Tests\Feature\OrganizationMember\DataProvider\OrganizationMemberAuthDataProvider;
use App\Tests\Feature\OrganizationMember\DataProvider\OrganizationMemberListDataProvider;
use App\Tests\Feature\OrganizationMember\DataProvider\OrganizationMemberNotFoundDataProvider;
use App\Tests\Feature\OrganizationMember\DataProvider\OrganizationMemberPatchDataProvider;
use App\Tests\Feature\OrganizationMember\DataProvider\OrganizationMemberScheduleAssignmentsListDataProvider;

class OrganizationMemberControllerTest extends BaseWebTestCase
{
    protected OrganizationRepository $organizationRepository;
    protected OrganizationMemberRepository $organizationMemberRepository;
    protected UserRepository $userRepository;
    protected ScheduleAssignmentRepository $scheduleAssignmentRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->organizationRepository = $this->container->get(OrganizationRepository::class);
        $this->organizationMemberRepository = $this->container->get(OrganizationMemberRepository::class);
        $this->userRepository = $this->container->get(UserRepository::class);
        $this->scheduleAssignmentRepository = $this->container->get(ScheduleAssignmentRepository::class);
    }

    #[Fixtures([OrganizationFixtures::class, UserFixtures::class])]
    #[DataProviderExternal(OrganizationMemberCreateDataProvider::class, 'validDataCases')]
    public function testCreateMember(array $params, array $expectedResponseData): void
    {
        $this->client->loginUser($this->user, 'api');
        $organization = $this->organizationRepository->findOneBy([]);
        $user = $this->userRepository->findOneBy(['email' => 'user1@example.com']);
        $params['user_id'] = $user->getId();
        $expectedResponseData['app_user'] = $this->normalize($user, UserNormalizerGroup::BASE_INFO->normalizationGroups());
        
        $responseData = $this->getSuccessfulResponseData($this->client,'POST', '/api/organizations/'.$organization->getId().'/members', $params);
        $this->assertIsInt($responseData['id']);
        $this->assertArrayIsEqualToArrayOnlyConsideringListOfKeys($expectedResponseData, $responseData, array_keys($expectedResponseData));
    }

    #[Fixtures([OrganizationFixtures::class, UserFixtures::class])]
    #[DataProviderExternal(OrganizationMemberCreateDataProvider::class, 'validationDataCases')]
    public function testAddMemberValidation(array $params, array $expectedErrors): void
    {
        $organization = $this->organizationRepository->findOneBy([]);
        $user = $this->userRepository->findOneBy(['email' => 'user1@example.com']);
        $params = array_merge(['user_id' => $user->getId()], $params);

        $this->client->loginUser($this->user, 'api');
        $this->assertPathValidation($this->client, 'POST', '/api/organizations/'.$organization->getId().'/members', $params, $expectedErrors);
    }

    #[Fixtures([OrganizationMemberFixtures::class])]
    public function testGet(): void
    {
        $organizationMember = $this->organizationMemberRepository->findOneBy([]);
        $expectedResponseData = $this->normalize($organizationMember, OrganizationMemberNormalizerGroup::PUBLIC->normalizationGroups());
        $organizationMemberId = $organizationMember->getId();
        $organizationId = $organizationMember->getOrganization()->getId();
        $responseData = $this->getSuccessfulResponseData($this->client, 'GET', "/api/organizations/$organizationId/members/$organizationMemberId");

        $this->assertEquals($expectedResponseData, $responseData);
    }

    #[Fixtures([OrganizationMemberFixtures::class])]
    #[DataProviderExternal(OrganizationMemberPatchDataProvider::class, 'validDataCases')]
    public function testPatch(array $params, array $expectedFieldValues): void
    {
        $organizationMember = $this->organizationMemberRepository->findOneBy(['role' => OrganizationRole::MEMBER]);
        $organizationMemberId = $organizationMember->getId();
        $organizationId = $organizationMember->getOrganization()->getId();
        $normalizedOrganizationMember = $this->normalize($organizationMember, OrganizationMemberNormalizerGroup::PRIVATE->normalizationGroups());
        $expectedResponseData = array_merge($normalizedOrganizationMember, $expectedFieldValues);
        $this->client->loginUser($this->user, 'api');
        $responseData = $this->getSuccessfulResponseData($this->client, 'PATCH', "/api/organizations/$organizationId/members/$organizationMemberId", $params);

        $this->assertEquals($expectedResponseData, $responseData);
    }

    #[Fixtures([OrganizationMemberFixtures::class])]
    #[DataProviderExternal(OrganizationMemberPatchDataProvider::class, 'validationDataCases')]
    public function testPatchMemberValidation(array $params, array $expectedErrors): void
    {
        $organizationMember = $this->organizationMemberRepository->findOneBy(['role' => OrganizationRole::MEMBER]);
        $organizationMemberId = $organizationMember->getId();
        $organizationId = $organizationMember->getOrganization()->getId();
        $this->client->loginUser($this->user, 'api');
        $this->assertPathValidation($this->client, 'PATCH', "/api/organizations/$organizationId/members/$organizationMemberId", $params, $expectedErrors);
    }

    #[Fixtures([OrganizationMemberFixtures::class])]
    #[DataProviderExternal(OrganizationMemberPatchDataProvider::class, 'conflictDataCases')]
    public function testPatchMemberConflictResponse(array $params, string $expectedMessage): void
    {
        $organizationMember = $this->organizationMemberRepository->findOneBy(['role' => OrganizationRole::ADMIN]);
        $organizationMemberId = $organizationMember->getId();
        $organizationId = $organizationMember->getOrganization()->getId();
        $this->client->loginUser($this->user, 'api');
        $responseData = $this->getFailureResponseData($this->client, 'PATCH', "/api/organizations/$organizationId/members/$organizationMemberId", $params, expectedCode: 409);

        $this->assertEquals($expectedMessage, $responseData['message']);
    }

    #[Fixtures([OrganizationMemberFixtures::class])]
    public function testDelete(): void
    {
        $organizationMember = $this->organizationMemberRepository->findOneBy(['role' => OrganizationRole::MEMBER]);
        $organizationMemberId = $organizationMember->getId();
        $organizationId = $organizationMember->getOrganization()->getId();
        $this->client->loginUser($this->user, 'api');
        $responseData = $this->getSuccessfulResponseData($this->client, 'DELETE', "/api/organizations/$organizationId/members/$organizationMemberId");

        $this->assertEquals('Organization member removed successfully', $responseData['message']);
    }

    #[Fixtures([OrganizationMemberFixtures::class])]
    public function testDeleteConflictResponse(): void
    {
        $organizationMember = $this->organizationMemberRepository->findOneBy(['role' => OrganizationRole::ADMIN]);
        $organizationMemberId = $organizationMember->getId();
        $organizationId = $organizationMember->getOrganization()->getId();
        $this->client->loginUser($this->user, 'api');
        $responseData = $this->getFailureResponseData($this->client, 'DELETE', "/api/organizations/$organizationId/members/$organizationMemberId", expectedCode: 409);

        $this->assertEquals('Cannot remove the only administrator of organization.', $responseData['message']);
    }

    #[Fixtures([OrganizationMemberFixtures::class])]
    #[DataProviderExternal(OrganizationMemberListDataProvider::class, 'listDataCases')]
    public function testList(int $page, int $perPage, int $total): void
    {
        $organization = $this->organizationRepository->findOneBy([]);
        $path = '/api/organizations/'.$organization->getId().'/members?' . http_build_query([
            'page' => $page,
            'per_page' => $perPage,
        ]);
        $responseData = $this->getSuccessfulResponseData($this->client, 'GET', $path);

        $offset = ($page - 1) * $perPage;
        $items = $this->organizationMemberRepository->findBy(['organization' => $organization], ['id' => 'ASC'], $perPage, $offset);
        $formattedItems = $this->normalize($items, OrganizationMemberNormalizerGroup::PUBLIC->normalizationGroups());

        $this->assertPaginatorResponse($responseData, $page, $perPage, $total, $formattedItems);
    }

    #[Fixtures([OrganizationMemberSortingFixtures::class])]
    #[DataProviderExternal(OrganizationMemberListDataProvider::class, 'filtersDataCases')]
    public function testListFilters(array $filters, array $expectedItemData): void
    {
        $organization = $this->organizationRepository->findOneBy([]);
        $path = '/api/organizations/'.$organization->getId().'/members?' . http_build_query(['filters' => $filters]);
        $responseData = $this->getSuccessfulResponseData($this->client, 'GET', $path);

        $this->assertCount(1, $responseData['items']);
        $dotExpectedItemData = array_dot($expectedItemData);
        $dotResponseItemData = array_dot($responseData['items'][0]);
        $this->assertArrayIsEqualToArrayOnlyConsideringListOfKeys($dotExpectedItemData, $dotResponseItemData, array_keys($dotExpectedItemData));
    }

    #[Fixtures([OrganizationMemberSortingFixtures::class])]
    #[DataProviderExternal(OrganizationMemberListDataProvider::class, 'sortingDataCases')]
    public function testListSorting(string $sorting, array $orderedItems): void
    {
        $organization = $this->organizationRepository->findOneBy([]);
        $path = '/api/organizations/'.$organization->getId().'/members?'. http_build_query(['order' => $sorting]);
        $responseData = $this->getSuccessfulResponseData($this->client, 'GET', $path);

        $this->assertGreaterThanOrEqual(count($orderedItems), count($responseData['items']));
        foreach($orderedItems as $indx => $item){
            $dotExpectedItemData = array_dot($item);
            $dotResponseItemData = array_dot($responseData['items'][$indx]);
            $this->assertArrayIsEqualToArrayOnlyConsideringListOfKeys($dotExpectedItemData, $dotResponseItemData, array_keys($dotExpectedItemData));
        }
    }

    #[Fixtures([OrganizationMemberSortingFixtures::class])]
    #[DataProviderExternal(OrganizationMemberListDataProvider::class, 'validationDataCases')]
    public function testListValidation(array $params, array $expectedErrors): void
    {
        $organization = $this->organizationRepository->findOneBy([]);
        $path = '/api/organizations/'.$organization->getId().'/members?' . http_build_query($params);
        $this->assertPathValidation($this->client, 'GET', $path, [], $expectedErrors);
    }

    #[Fixtures([OrganizationMemberScheduleAssignmentsFixtures::class])]
    #[DataProviderExternal(OrganizationMemberScheduleAssignmentsListDataProvider::class, 'listDataCases')]
    public function testListOrganizationMemberScheduleAssignments(int $page, int $perPage, int $total): void
    {
        $organizationMember = $this->organizationMemberRepository->findOneBy(['role' => OrganizationRole::MEMBER->value]);
        $organizationMemberId = $organizationMember->getId();
        $organizationId = $organizationMember->getOrganization()->getId();
        $path = "/api/organizations/$organizationId/members/$organizationMemberId/schedule-assignments?" . http_build_query([
            'page' => $page,
            'per_page' => $perPage,
        ]);
        $this->client->loginUser($organizationMember->getAppUser(), 'api');
        $responseData = $this->getSuccessfulResponseData($this->client, 'GET', $path);

        $offset = ($page - 1) * $perPage;
        $items = $this->scheduleAssignmentRepository->findBy(['organizationMember' => $organizationMember], ['id' => 'ASC'], $perPage, $offset);
        $formattedItems = $this->normalize($items, ScheduleAssignmentNormalizerGroup::ORGANIZATION_MEMBER_SCHEDULE_ASSIGNMENTS->normalizationGroups());

        $this->assertPaginatorResponse($responseData, $page, $perPage, $total, $formattedItems);
    }

    #[Fixtures([OrganizationMemberScheduleAssignmentsSortingFixtures::class])]
    #[DataProviderExternal(OrganizationMemberScheduleAssignmentsListDataProvider::class, 'filtersDataCases')]
    public function testListOrganizationMemberScheduleAssignmentsFilters(array $filters, array $expectedItemData): void
    {
        $organizationMember = $this->organizationMemberRepository->findOneBy(['role' => OrganizationRole::MEMBER->value]);
        $organizationMemberId = $organizationMember->getId();
        $organizationId = $organizationMember->getOrganization()->getId();
        $path = "/api/organizations/$organizationId/members/$organizationMemberId/schedule-assignments?" . http_build_query(['filters' => $filters]);
        $this->client->loginUser($this->user, 'api');
        $responseData = $this->getSuccessfulResponseData($this->client, 'GET', $path);

        $this->assertCount(1, $responseData['items']);
        $dotExpectedItemData = array_dot($expectedItemData);
        $dotResponseItemData = array_dot($responseData['items'][0]);
        $this->assertArrayIsEqualToArrayOnlyConsideringListOfKeys($dotExpectedItemData, $dotResponseItemData, array_keys($dotExpectedItemData));
    }

    #[Fixtures([OrganizationMemberScheduleAssignmentsSortingFixtures::class])]
    #[DataProviderExternal(OrganizationMemberScheduleAssignmentsListDataProvider::class, 'sortingDataCases')]
    public function testListOrganizationMemberScheduleAssignmentsSorting(string $sorting, array $orderedItems): void
    {
        $organizationMember = $this->organizationMemberRepository->findOneBy(['role' => OrganizationRole::MEMBER->value]);
        $organizationMemberId = $organizationMember->getId();
        $organizationId = $organizationMember->getOrganization()->getId();
        $path = "/api/organizations/$organizationId/members/$organizationMemberId/schedule-assignments?" . http_build_query(['order' => $sorting]);
        $this->client->loginUser($this->user, 'api');
        $responseData = $this->getSuccessfulResponseData($this->client, 'GET', $path);

        $this->assertGreaterThanOrEqual(count($orderedItems), count($responseData['items']));
        foreach($orderedItems as $indx => $item){
            $dotExpectedItemData = array_dot($item);
            $dotResponseItemData = array_dot($responseData['items'][$indx]);
            $this->assertArrayIsEqualToArrayOnlyConsideringListOfKeys($dotExpectedItemData, $dotResponseItemData, array_keys($dotExpectedItemData));
        }
    }

    #[Fixtures([OrganizationMemberScheduleAssignmentsSortingFixtures::class])]
    #[DataProviderExternal(OrganizationMemberScheduleAssignmentsListDataProvider::class, 'validationDataCases')]
    public function testListOrganizationMemberScheduleAssignmentsValidation(array $params, array $expectedErrors): void
    {
        $organizationMember = $this->organizationMemberRepository->findOneBy(['role' => OrganizationRole::MEMBER->value]);
        $organizationMemberId = $organizationMember->getId();
        $organizationId = $organizationMember->getOrganization()->getId();
        $path = "/api/organizations/$organizationId/members/$organizationMemberId/schedule-assignments?" . http_build_query($params);
        $this->client->loginUser($this->user, 'api');
        $this->assertPathValidation($this->client, 'GET', $path, [], $expectedErrors);
    }

    #[Fixtures([OrganizationMemberFixtures::class])]
    #[DataProviderExternal(OrganizationMemberNotFoundDataProvider::class, 'dataCases')]
    public function testNotFoundResponses(string $path, string $method, string $expectedMessage): void
    {
        $organization = $this->organizationRepository->findOneBy([]);
        $path = str_replace('{organization}', (string)($organization->getId()), $path);
        $this->client->loginUser($this->user, 'api');
        $responseData = $this->getFailureResponseData($this->client, $method, $path, expectedCode: 404);
        $this->assertEquals($expectedMessage, $responseData['message']);
    }

    #[Fixtures([OrganizationMemberFixtures::class])]
    #[DataProviderExternal(OrganizationMemberAuthDataProvider::class, 'protectedPaths')]
    public function testAuthRequirementForProtectedPaths(string $path, string $method): void
    {
        $organization = $this->organizationRepository->findOneBy([]);
        $organizationMember = $this->organizationMemberRepository->findOneBy([]);
        $path = str_replace('{organization}', (string)($organization->getId()), $path);
        $path = str_replace('{organizationMember}', (string)($organizationMember->getId()), $path);

        $this->assertPathIsProtected($path, $method);
    }

    #[Fixtures([UserFixtures::class, OrganizationMemberFixtures::class])]
    #[DataProviderExternal(OrganizationMemberAuthDataProvider::class, 'privilegesOnlyPaths')]
    public function testPrivilegesRequirementForProtectedPaths(string $path, string $method, string $userEmail): void
    {
        $organization = $this->organizationRepository->findOneBy([]);
        $organizationMember = $this->organizationMemberRepository->findOneBy([]);
        $path = str_replace('{organization}', (string)($organization->getId()), $path);
        $path = str_replace('{organizationMember}', (string)($organizationMember->getId()), $path);
        $user = $this->userRepository->findOneBy(['email' => $userEmail]);

        $this->client->loginUser($user, 'api');
        $responseData = $this->getFailureResponseData($this->client, $method, $path, expectedCode: 403);
        $this->assertEquals(ForbiddenResponse::RESPONSE_MESSAGE, $responseData['message']);
    }
}
