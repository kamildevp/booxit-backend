<?php

declare(strict_types=1);

namespace App\Tests\Feature\ScheduleAssignment;

use App\DataFixtures\Test\OrganizationMember\OrganizationMemberFixtures;
use App\DataFixtures\Test\Schedule\ScheduleFixtures;
use App\DataFixtures\Test\ScheduleAssignment\ScheduleAssignmentConflictFixtures;
use App\DataFixtures\Test\ScheduleAssignment\ScheduleAssignmentFixtures;
use App\DataFixtures\Test\ScheduleAssignment\ScheduleAssignmentSortingFixtures;
use App\DataFixtures\Test\User\UserFixtures;
use App\Entity\OrganizationMember;
use App\Enum\Organization\OrganizationRole;
use App\Enum\OrganizationMember\OrganizationMemberNormalizerGroup;
use App\Enum\Schedule\ScheduleAccessType;
use App\Enum\ScheduleAssignment\ScheduleAssignmentNormalizerGroup;
use App\Repository\ScheduleAssignmentRepository;
use App\Repository\ScheduleRepository;
use App\Repository\OrganizationMemberRepository;
use App\Repository\OrganizationRepository;
use App\Repository\UserRepository;
use App\Response\ForbiddenResponse;
use App\Tests\Utils\Attribute\Fixtures;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use App\Tests\Utils\BaseWebTestCase;
use App\Tests\Feature\ScheduleAssignment\DataProvider\ScheduleAssignmentCreateDataProvider;
use App\Tests\Feature\ScheduleAssignment\DataProvider\ScheduleAssignmentAuthDataProvider;
use App\Tests\Feature\ScheduleAssignment\DataProvider\ScheduleAssignmentListDataProvider;
use App\Tests\Feature\ScheduleAssignment\DataProvider\ScheduleAssignmentNotFoundDataProvider;
use App\Tests\Feature\ScheduleAssignment\DataProvider\ScheduleAssignmentPatchDataProvider;

class ScheduleAssignmentControllerTest extends BaseWebTestCase
{
    protected UserRepository $userRepository;
    protected OrganizationRepository $organizationRepository;
    protected ScheduleRepository $scheduleRepository;
    protected ScheduleAssignmentRepository $scheduleAssignmentRepository;
    protected OrganizationMemberRepository $organizationMemberRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepository = $this->container->get(UserRepository::class);
        $this->organizationRepository = $this->container->get(OrganizationRepository::class);
        $this->scheduleRepository = $this->container->get(ScheduleRepository::class);
        $this->scheduleAssignmentRepository = $this->container->get(ScheduleAssignmentRepository::class);
        $this->organizationMemberRepository = $this->container->get(OrganizationMemberRepository::class);
    }

    #[Fixtures([OrganizationMemberFixtures::class, ScheduleFixtures::class])]
    #[DataProviderExternal(ScheduleAssignmentCreateDataProvider::class, 'validDataCases')]
    public function testCreate(array $params, array $expectedResponseData): void
    {
        $this->client->loginUser($this->user, 'api');
        $schedule = $this->scheduleRepository->findOneBy([]);
        $organizationMember = $this->organizationMemberRepository->findOneBy(['role' => OrganizationRole::MEMBER->value]);
        $params['organization_member_id'] = $organizationMember->getId();
        $expectedResponseData['organization_member'] = $this->normalize($organizationMember, OrganizationMemberNormalizerGroup::PUBLIC->normalizationGroups());
        
        $responseData = $this->getSuccessfulResponseData($this->client,'POST', '/api/schedules/'.$schedule->getId().'/assignments', $params);
        $this->assertIsInt($responseData['id']);
        $this->assertArrayIsEqualToArrayOnlyConsideringListOfKeys($expectedResponseData, $responseData, array_keys($expectedResponseData));
    }

    #[Fixtures([OrganizationMemberFixtures::class, ScheduleFixtures::class])]
    #[DataProviderExternal(ScheduleAssignmentCreateDataProvider::class, 'validationDataCases')]
    public function testCreateValidation(array $params, array $expectedErrors): void
    {
        $schedule = $this->scheduleRepository->findOneBy([]);

        $this->client->loginUser($this->user, 'api');
        $this->assertPathValidation($this->client, 'POST', '/api/schedules/'.$schedule->getId().'/assignments', $params, $expectedErrors);
    }

    #[Fixtures([ScheduleFixtures::class, ScheduleAssignmentConflictFixtures::class])]
    #[DataProviderExternal(ScheduleAssignmentCreateDataProvider::class, 'validDataCases')]
    public function testCreateConflictResponseForInvalidOrganizationMember(array $params): void
    {
        $schedule = $this->scheduleRepository->findOneBy([]); 
        $secondOrganization = $this->organizationRepository->findOneBy(['name' => ScheduleAssignmentConflictFixtures::ORGANIZATION_NAME]);
        $invalidOrganizationMember = $this->organizationMemberRepository->findOneBy(['organization' => $secondOrganization]);
        $params['organization_member_id'] = $invalidOrganizationMember->getId();

        $this->client->loginUser($this->user, 'api');
        $responseData = $this->getFailureResponseData($this->client, 'POST', '/api/schedules/'.$schedule->getId().'/assignments', $params, expectedCode: 409);
        $this->assertEquals('This organization member belongs to different organization.', $responseData['message']);
    }

    #[Fixtures([ScheduleAssignmentFixtures::class])]
    #[DataProviderExternal(ScheduleAssignmentCreateDataProvider::class, 'validDataCases')]
    public function testCreateConflictResponseForExistingAssignment(array $params): void
    {
        $schedule = $this->scheduleRepository->findOneBy([]); 
        $assignedOrganizationMember = $this->scheduleAssignmentRepository->findOneBy([])->getOrganizationMember();
        $params['organization_member_id'] = $assignedOrganizationMember->getId();

        $this->client->loginUser($this->user, 'api');
        $responseData = $this->getFailureResponseData($this->client, 'POST', '/api/schedules/'.$schedule->getId().'/assignments', $params, expectedCode: 409);
        $this->assertEquals('This organization member is already assigned to this schedule.', $responseData['message']);
    }


    #[Fixtures([ScheduleAssignmentFixtures::class])]
    public function testGet(): void
    {
        $scheduleAssignment = $this->scheduleAssignmentRepository->findOneBy([]);
        $expectedResponseData = $this->normalize($scheduleAssignment, ScheduleAssignmentNormalizerGroup::PUBLIC->normalizationGroups());
        $scheduleAssignmentId = $scheduleAssignment->getId();
        $scheduleId = $scheduleAssignment->getSchedule()->getId();

        $this->client->loginUser($this->user, 'api');
        $responseData = $this->getSuccessfulResponseData($this->client, 'GET', "/api/schedules/$scheduleId/assignments/$scheduleAssignmentId");
        $this->assertEquals($expectedResponseData, $responseData);
    }

    #[Fixtures([ScheduleAssignmentFixtures::class])]
    #[DataProviderExternal(ScheduleAssignmentPatchDataProvider::class, 'validDataCases')]
    public function testPatch(array $params, array $expectedFieldValues): void
    {
        $scheduleAssignment = $this->scheduleAssignmentRepository->findOneBy(['accessType' => ScheduleAccessType::READ]);
        $scheduleAssignmentId = $scheduleAssignment->getId();
        $scheduleId = $scheduleAssignment->getSchedule()->getId();
        $normalizedScheduleAssignment = $this->normalize($scheduleAssignment, ScheduleAssignmentNormalizerGroup::PRIVATE->normalizationGroups());
        $expectedResponseData = array_merge($normalizedScheduleAssignment, $expectedFieldValues);
        
        $this->client->loginUser($this->user, 'api');
        $responseData = $this->getSuccessfulResponseData($this->client, 'PATCH', "/api/schedules/$scheduleId/assignments/$scheduleAssignmentId", $params);
        $this->assertEquals($expectedResponseData, $responseData);
    }

    #[Fixtures([ScheduleAssignmentFixtures::class])]
    #[DataProviderExternal(ScheduleAssignmentPatchDataProvider::class, 'validationDataCases')]
    public function testPatchMemberValidation(array $params, array $expectedErrors): void
    {
        $scheduleAssignment = $this->scheduleAssignmentRepository->findOneBy(['accessType' => ScheduleAccessType::READ]);
        $scheduleAssignmentId = $scheduleAssignment->getId();
        $scheduleId = $scheduleAssignment->getSchedule()->getId();
        $this->client->loginUser($this->user, 'api');
        $this->assertPathValidation($this->client, 'PATCH', "/api/schedules/$scheduleId/assignments/$scheduleAssignmentId", $params, $expectedErrors);
    }

    #[Fixtures([ScheduleAssignmentFixtures::class])]
    public function testDelete(): void
    {
        $scheduleAssignment = $this->scheduleAssignmentRepository->findOneBy(['accessType' => ScheduleAccessType::READ]);
        $scheduleAssignmentId = $scheduleAssignment->getId();
        $scheduleId = $scheduleAssignment->getSchedule()->getId();

        $this->client->loginUser($this->user, 'api');
        $responseData = $this->getSuccessfulResponseData($this->client, 'DELETE', "/api/schedules/$scheduleId/assignments/$scheduleAssignmentId");
        $this->assertEquals('Schedule assignment removed successfully', $responseData['message']);
    }

    #[Fixtures([ScheduleAssignmentFixtures::class])]
    #[DataProviderExternal(ScheduleAssignmentListDataProvider::class, 'listDataCases')]
    public function testList(int $page, int $perPage, int $total): void
    {
        $schedule = $this->scheduleRepository->findOneBy([]);
        $path = '/api/schedules/'.$schedule->getId().'/assignments?' . http_build_query([
            'page' => $page,
            'per_page' => $perPage,
        ]);
        $this->client->loginUser($this->user, 'api');
        $responseData = $this->getSuccessfulResponseData($this->client, 'GET', $path);

        $offset = ($page - 1) * $perPage;
        $items = $this->scheduleAssignmentRepository->findBy(['schedule' => $schedule], ['id' => 'ASC'], $perPage, $offset);
        $formattedItems = $this->normalize($items, ScheduleAssignmentNormalizerGroup::PUBLIC->normalizationGroups());

        $this->assertPaginatorResponse($responseData, $page, $perPage, $total, $formattedItems);
    }

    #[Fixtures([ScheduleAssignmentSortingFixtures::class])]
    #[DataProviderExternal(ScheduleAssignmentListDataProvider::class, 'filtersDataCases')]
    public function testListFilters(array $filters, array $expectedItemData): void
    {
        $schedule = $this->scheduleRepository->findOneBy([]);
        $path = '/api/schedules/'.$schedule->getId().'/assignments?' . http_build_query(['filters' => $filters]);
        $this->client->loginUser($this->user, 'api');
        $responseData = $this->getSuccessfulResponseData($this->client, 'GET', $path);

        $this->assertCount(1, $responseData['items']);
        $dotExpectedItemData = array_dot($expectedItemData);
        $dotResponseItemData = array_dot($responseData['items'][0]);
        $this->assertArrayIsEqualToArrayOnlyConsideringListOfKeys($dotExpectedItemData, $dotResponseItemData, array_keys($dotExpectedItemData));
    }

    #[Fixtures([ScheduleAssignmentSortingFixtures::class])]
    #[DataProviderExternal(ScheduleAssignmentListDataProvider::class, 'sortingDataCases')]
    public function testListSorting(string $sorting, array $orderedItems): void
    {
        $schedule = $this->scheduleRepository->findOneBy([]);
        $path = '/api/schedules/'.$schedule->getId().'/assignments?'. http_build_query(['order' => $sorting]);
        $this->client->loginUser($this->user, 'api');
        $responseData = $this->getSuccessfulResponseData($this->client, 'GET', $path);

        $this->assertGreaterThanOrEqual(count($orderedItems), count($responseData['items']));
        foreach($orderedItems as $indx => $item){
            $dotExpectedItemData = array_dot($item);
            $dotResponseItemData = array_dot($responseData['items'][$indx]);
            $this->assertArrayIsEqualToArrayOnlyConsideringListOfKeys($dotExpectedItemData, $dotResponseItemData, array_keys($dotExpectedItemData));
        }
    }

    #[Fixtures([ScheduleAssignmentFixtures::class])]
    #[DataProviderExternal(ScheduleAssignmentListDataProvider::class, 'validationDataCases')]
    public function testListValidation(array $params, array $expectedErrors): void
    {
        $schedule = $this->scheduleRepository->findOneBy([]);
        $path = '/api/schedules/'.$schedule->getId().'/assignments?' . http_build_query($params);
        $this->client->loginUser($this->user, 'api');
        $this->assertPathValidation($this->client, 'GET', $path, [], $expectedErrors);
    }

    #[Fixtures([ScheduleAssignmentFixtures::class])]
    #[DataProviderExternal(ScheduleAssignmentNotFoundDataProvider::class, 'dataCases')]
    public function testNotFoundResponses(string $path, string $method, string $expectedMessage): void
    {
        $schedule = $this->scheduleRepository->findOneBy([]);
        $path = str_replace('{schedule}', (string)($schedule->getId()), $path);
        $this->client->loginUser($this->user, 'api');
        $responseData = $this->getFailureResponseData($this->client, $method, $path, expectedCode: 404);
        $this->assertEquals($expectedMessage, $responseData['message']);
    }

    #[Fixtures([ScheduleAssignmentFixtures::class])]
    #[DataProviderExternal(ScheduleAssignmentAuthDataProvider::class, 'protectedPaths')]
    public function testAuthRequirementForProtectedPaths(string $path, string $method): void
    {
        $schedule = $this->scheduleRepository->findOneBy([]);
        $scheduleAssignment = $this->scheduleAssignmentRepository->findOneBy([]);
        $path = str_replace('{schedule}', (string)($schedule->getId()), $path);
        $path = str_replace('{scheduleAssignment}', (string)($scheduleAssignment->getId()), $path);

        $this->assertPathIsProtected($path, $method);
    }

    #[Fixtures([UserFixtures::class, ScheduleAssignmentFixtures::class])]
    #[DataProviderExternal(ScheduleAssignmentAuthDataProvider::class, 'scheduleManagementPrivilegesOnlyPaths')]
    public function testScheduleManagementPrivilegesRequirementForProtectedPaths(string $path, string $method, ?string $organizationRole): void
    {
        $schedule = $this->scheduleRepository->findOneBy([]);
        $scheduleAssignment = $this->scheduleAssignmentRepository->findOneBy([]);
        $path = str_replace('{schedule}', (string)($schedule->getId()), $path);
        $path = str_replace('{scheduleAssignment}', (string)($scheduleAssignment->getId()), $path);
        $user = $this->userRepository->findOneBy(['email' => 'user1@example.com']);
        if(!empty($organizationRole)){
            $organizationMember = new OrganizationMember();
            $organizationMember->setOrganization($schedule->getOrganization());
            $organizationMember->setAppUser($user);
            $organizationMember->setRole($organizationRole);
            $this->organizationMemberRepository->save($organizationMember, true);
        }

        $this->client->loginUser($user, 'api');
        $responseData = $this->getFailureResponseData($this->client, $method, $path, expectedCode: 403);
        $this->assertEquals(ForbiddenResponse::RESPONSE_MESSAGE, $responseData['message']);
    }
}
