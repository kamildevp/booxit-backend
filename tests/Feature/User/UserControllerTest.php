<?php

declare(strict_types=1);

namespace App\Tests\Feature\User;

use App\DataFixtures\Test\Organization\OrganizationFixtures;
use App\DataFixtures\Test\Organization\OrganizationSortingFixtures;
use App\DataFixtures\Test\OrganizationMember\OrganizationMemberFixtures;
use App\DataFixtures\Test\User\ChangeUserPasswordFixtures;
use App\DataFixtures\Test\User\PasswordResetFixtures;
use App\DataFixtures\Test\User\UserFixtures;
use App\DataFixtures\Test\User\UserSortingFixtures;
use App\DataFixtures\Test\User\VerifyUserFixtures;
use App\Enum\EmailConfirmationType;
use App\Enum\OrganizationMember\OrganizationMemberNormalizerGroup;
use App\Enum\User\UserNormalizerGroup;
use App\Repository\OrganizationMemberRepository;
use App\Repository\UserRepository;
use App\Tests\Feature\Attribute\Fixtures;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use App\Tests\Feature\BaseWebTestCase;
use App\Tests\Feature\Trait\EmailConfirmationUtils;
use App\Tests\Feature\User\DataProvider\UserAuthDataProvider;
use App\Tests\Feature\User\DataProvider\UserChangePasswordDataProvider;
use App\Tests\Feature\User\DataProvider\UserCreateDataProvider;
use App\Tests\Feature\User\DataProvider\UserListDataProvider;
use App\Tests\Feature\User\DataProvider\UserNotFoundDataProvider;
use App\Tests\Feature\User\DataProvider\UserOrganizationMembershipListDataProvider;
use App\Tests\Feature\User\DataProvider\UserPatchDataProvider;
use App\Tests\Feature\User\DataProvider\UserResetPasswordDataProvider;
use App\Tests\Feature\User\DataProvider\UserResetPasswordRequestDataProvider;
use App\Tests\Feature\User\DataProvider\UserVerifyDataProvider;
use Symfony\Component\Messenger\Transport\InMemory\InMemoryTransport;

class UserControllerTest extends BaseWebTestCase
{
    use EmailConfirmationUtils;

    protected InMemoryTransport $mailerTransport;
    protected UserRepository $userRepository;
    protected OrganizationMemberRepository $organizationMemberRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mailerTransport = $this->container->get('messenger.transport.async_mailer');
        $this->userRepository = $this->container->get(UserRepository::class);
        $this->organizationMemberRepository = $this->container->get(OrganizationMemberRepository::class);
    }

    #[DataProviderExternal(UserCreateDataProvider::class, 'validDataCases')]
    public function testCreate(array $params, array $expectedResponseData): void
    {
        $responseData = $this->getSuccessfulResponseData($this->client,'POST', '/api/user', $params);
        $this->assertIsInt($responseData['id']);
        $this->assertArrayIsEqualToArrayOnlyConsideringListOfKeys($expectedResponseData, $responseData, array_keys($expectedResponseData));
        $this->assertCount(1, $this->mailerTransport->getSent());
    }

    #[Fixtures([UserFixtures::class])]
    #[DataProviderExternal(UserCreateDataProvider::class, 'validationDataCases')]
    public function testCreateValidation(array $params, array $expectedErrors): void
    {
        $this->assertPathValidation($this->client, 'POST', '/api/user', $params, $expectedErrors);
        $this->assertCount(0, $this->mailerTransport->getSent());
    }

    #[Fixtures([VerifyUserFixtures::class])]
    public function testVerifySuccess(): void
    {
        $params = $this->prepareEmailConfirmationVerifyParams(EmailConfirmationType::USER_VERIFICATION);
        $responseData = $this->getSuccessfulResponseData($this->client, 'POST', '/api/user/verify', $params);

        $this->assertEquals('Verification Successful', $responseData['message']);
    }

    #[Fixtures([VerifyUserFixtures::class])]
    #[DataProviderExternal(UserVerifyDataProvider::class, 'failureDataCases')]
    public function testVerifyFailure(array $verifyParams): void
    {
        $validParams = $this->prepareEmailConfirmationVerifyParams(EmailConfirmationType::USER_VERIFICATION);
        $params = array_merge($validParams, $verifyParams);
        $responseData = $this->getFailureResponseData($this->client, 'POST', '/api/user/verify', $params);

        $this->assertEquals('Verification Failed', $responseData['message']);
    }

    #[DataProviderExternal(UserVerifyDataProvider::class, 'validationDataCases')]
    public function testVerifyValidation(array $params, array $expectedErrors): void
    {
        $this->assertPathValidation($this->client, 'POST', '/api/user/verify', $params, $expectedErrors);
    }

    public function testMe(): void
    {
        $expectedResponseData = $this->normalize($this->user, UserNormalizerGroup::PRIVATE->normalizationGroups());
        $this->client->loginUser($this->user, 'api');
        $responseData = $this->getSuccessfulResponseData($this->client, 'GET', '/api/user/me');

        $this->assertEquals($expectedResponseData, $responseData);
    }

    #[Fixtures([UserFixtures::class])]
    public function testGet(): void
    {
        $user = $this->userRepository->findOneBy(['email' => 'user1@example.com']);
        $expectedResponseData = $this->normalize($user, UserNormalizerGroup::PUBLIC->normalizationGroups());
        $userId = $user->getId();
        $responseData = $this->getSuccessfulResponseData($this->client, 'GET', "/api/user/$userId");

        $this->assertEquals($expectedResponseData, $responseData);
    }

    #[DataProviderExternal(UserPatchDataProvider::class, 'validDataCases')]
    public function testPatch(array $params, array $expectedFieldValues, bool $mailSent): void
    {
        $normalizedUser = $this->normalize($this->user, UserNormalizerGroup::PRIVATE->normalizationGroups());
        $expectedResponseData = array_merge($normalizedUser, $expectedFieldValues);
        $this->client->loginUser($this->user, 'api');
        $responseData = $this->getSuccessfulResponseData($this->client, 'PATCH', '/api/user/me', $params);

        $this->assertArrayIsEqualToArrayIgnoringListOfKeys($expectedResponseData, $responseData, ['updated_at']);
        $this->assertCount($mailSent ? 1 : 0, $this->mailerTransport->getSent());
    }

    #[Fixtures([UserFixtures::class])]
    #[DataProviderExternal(UserPatchDataProvider::class, 'validationDataCases')]
    public function testPatchValidation(array $params, array $expectedErrors): void
    {
        $this->client->loginUser($this->user, 'api');
        $this->assertPathValidation($this->client, 'PATCH', '/api/user/me', $params, $expectedErrors);
        $this->assertCount(0, $this->mailerTransport->getSent());
    }

    #[Fixtures([ChangeUserPasswordFixtures::class])]
    #[DataProviderExternal(UserChangePasswordDataProvider::class, 'validDataCases')]
    public function testChangePassword(array $params, int $expectedRefreshTokensCount): void
    {
        $this->fullLogin($this->client);
        $responseData = $this->getSuccessfulResponseData($this->client, 'PATCH', '/api/user/change_password', $params);

        $this->assertEquals('Password changed successfully', $responseData['message']);
        $this->assertCount($expectedRefreshTokensCount, $this->user->getRefreshTokens());
    }

    #[DataProviderExternal(UserChangePasswordDataProvider::class, 'validationDataCases')]
    public function testChangePasswordValidation(array $params, array $expectedErrors): void
    {
        $this->client->loginUser($this->user, 'api');
        $this->assertPathValidation($this->client, 'PATCH', '/api/user/change_password', $params, $expectedErrors);
        $this->assertCount(0, $this->mailerTransport->getSent());
    }

    public function testDelete(): void
    {
        $this->client->loginUser($this->user, 'api');
        $responseData = $this->getSuccessfulResponseData($this->client, 'DELETE', '/api/user/me');

        $this->assertEquals('User removed successfully', $responseData['message']);
    }

    #[Fixtures([UserFixtures::class, OrganizationMemberFixtures::class])]
    public function testDeleteConflictResponse(): void
    {
        $this->client->loginUser($this->user, 'api');
        $responseData = $this->getFailureResponseData($this->client, 'DELETE', '/api/user/me', expectedCode: 409);

        $this->assertEquals('This user cannot be removed because they are the sole administrator of one or more organizations. Please remove those organizations first.', $responseData['message']);
    }

    #[Fixtures([UserFixtures::class], false)]
    #[DataProviderExternal(UserListDataProvider::class, 'listDataCases')]
    public function testList(int $page, int $perPage, int $total): void
    {
        $path = '/api/user?' . http_build_query([
            'page' => $page,
            'per_page' => $perPage,
        ]);
        $responseData = $this->getSuccessfulResponseData($this->client, 'GET', $path);

        $offset = ($page - 1) * $perPage;
        $items = $this->userRepository->findBy([], ['id' => 'ASC'], $perPage, $offset);
        $formattedItems = $this->normalize($items, UserNormalizerGroup::PUBLIC->normalizationGroups());

        $this->assertPaginatorResponse($responseData, $page, $perPage, $total, $formattedItems);
    }

    #[Fixtures([UserSortingFixtures::class], false)]
    #[DataProviderExternal(UserListDataProvider::class, 'filtersDataCases')]
    public function testListFilters(array $filters, array $expectedItemData): void
    {
        $path = '/api/user?' . http_build_query(['filters' => $filters]);
        $responseData = $this->getSuccessfulResponseData($this->client, 'GET', $path);

        $this->assertCount(1, $responseData['items']);
        $this->assertArrayIsEqualToArrayOnlyConsideringListOfKeys($expectedItemData, $responseData['items'][0], array_keys($expectedItemData));
    }

    #[Fixtures([UserSortingFixtures::class], false)]
    #[DataProviderExternal(UserListDataProvider::class, 'sortingDataCases')]
    public function testListSorting(string $sorting, array $orderedItems): void
    {
        $path = '/api/user?' . http_build_query(['order' => $sorting]);
        $responseData = $this->getSuccessfulResponseData($this->client, 'GET', $path);

        $this->assertGreaterThanOrEqual(count($orderedItems), count($responseData['items']));

        foreach($orderedItems as $indx => $item){
            $this->assertArrayIsEqualToArrayOnlyConsideringListOfKeys($item, $responseData['items'][$indx], array_keys($item));
        }
    }

    #[DataProviderExternal(UserListDataProvider::class, 'validationDataCases')]
    public function testListValidation(array $params, array $expectedErrors): void
    {
        $path = '/api/user?' . http_build_query($params);
        $this->assertPathValidation($this->client, 'GET', $path, [], $expectedErrors);
    }

    #[Fixtures([UserFixtures::class], false)]
    #[DataProviderExternal(UserResetPasswordRequestDataProvider::class, 'validDataCases')]
    public function testResetPasswordRequest(array $params): void
    {
        $expectedResponseData = ['message' => 'If user with specified email exists, password reset link was sent to specified email'];
        $responseData = $this->getSuccessfulResponseData($this->client,'POST', '/api/user/reset_password_request', $params);

        $this->assertEquals($expectedResponseData, $responseData);
        $this->assertCount(1, $this->mailerTransport->getSent());
    }

    #[DataProviderExternal(UserResetPasswordRequestDataProvider::class, 'validationDataCases')]
    public function testResetPasswordRequestValidation(array $params, array $expectedErrors): void
    {
        $this->assertPathValidation($this->client, 'POST', '/api/user/reset_password_request', $params, $expectedErrors);
        $this->assertCount(0, $this->mailerTransport->getSent());
    }

    #[Fixtures([PasswordResetFixtures::class])]
    #[DataProviderExternal(UserResetPasswordDataProvider::class, 'validDataCases')]
    public function testResetPasswordSuccess(array $params): void
    {
        $verifyParams = $this->prepareEmailConfirmationVerifyParams(EmailConfirmationType::PASSWORD_RESET);
        $params = array_merge($verifyParams, $params);
        $responseData = $this->getSuccessfulResponseData($this->client, 'PATCH', '/api/user/reset_password', $params);

        $this->assertEquals('Password reset successful', $responseData['message']);
    }

    #[Fixtures([PasswordResetFixtures::class])]
    #[DataProviderExternal(UserResetPasswordDataProvider::class, 'failureDataCases')]
    public function testResetPasswordFailure(array $params): void
    {
        $verifyParams = $this->prepareEmailConfirmationVerifyParams(EmailConfirmationType::PASSWORD_RESET);
        $params = array_merge($verifyParams, $params);
        $responseData = $this->getFailureResponseData($this->client, 'PATCH', '/api/user/reset_password', $params);
        
        $this->assertEquals('Password reset failed', $responseData['message']);
    }

    #[DataProviderExternal(UserResetPasswordDataProvider::class, 'validationDataCases')]
    public function testResetPasswordValidation(array $params, array $expectedErrors): void
    {
        $this->assertPathValidation($this->client, 'PATCH', '/api/user/reset_password', $params, $expectedErrors);
    }

    #[Fixtures([OrganizationFixtures::class])]
    #[DataProviderExternal(UserOrganizationMembershipListDataProvider::class, 'listDataCases')]
    public function testListOrganizationMemberships(int $page, int $perPage, int $total): void
    {
        $path = '/api/user/'.$this->user->getId().'/organization-membership?' . http_build_query([
            'page' => $page,
            'per_page' => $perPage,
        ]);
        $responseData = $this->getSuccessfulResponseData($this->client, 'GET', $path);

        $offset = ($page - 1) * $perPage;
        $items = $this->organizationMemberRepository->findBy(['appUser' => $this->user], ['id' => 'ASC'], $perPage, $offset);
        $formattedItems = $this->normalize($items, OrganizationMemberNormalizerGroup::USER_MEMBERSHIPS->normalizationGroups());

        $this->assertPaginatorResponse($responseData, $page, $perPage, $total, $formattedItems);
    }

    #[Fixtures([OrganizationSortingFixtures::class])]
    #[DataProviderExternal(UserOrganizationMembershipListDataProvider::class, 'filtersDataCases')]
    public function testListOrganizationMembershipsFilters(array $filters, array $expectedItemData): void
    {
        $path = '/api/user/'.$this->user->getId().'/organization-membership?' . http_build_query(['filters' => $filters]);
        $responseData = $this->getSuccessfulResponseData($this->client, 'GET', $path);

        $this->assertCount(1, $responseData['items']);
        $dotExpectedItemData = array_dot($expectedItemData);
        $dotResponseItemData = array_dot($responseData['items'][0]);
        $this->assertArrayIsEqualToArrayOnlyConsideringListOfKeys($dotExpectedItemData, $dotResponseItemData, array_keys($dotExpectedItemData));
    }

    #[Fixtures([OrganizationSortingFixtures::class])]
    #[DataProviderExternal(UserOrganizationMembershipListDataProvider::class, 'sortingDataCases')]
    public function testListOrganizationMembershipsSorting(string $sorting, array $orderedItems): void
    {
        $path = '/api/user/'.$this->user->getId().'/organization-membership?' . http_build_query(['order' => $sorting]);
        $responseData = $this->getSuccessfulResponseData($this->client, 'GET', $path);

        $this->assertGreaterThanOrEqual(count($orderedItems), count($responseData['items']));
        foreach($orderedItems as $indx => $item){
            $dotExpectedItemData = array_dot($item);
            $dotResponseItemData = array_dot($responseData['items'][$indx]);
            $this->assertArrayIsEqualToArrayOnlyConsideringListOfKeys($dotExpectedItemData, $dotResponseItemData, array_keys($dotExpectedItemData));
        }
    }

    #[DataProviderExternal(UserOrganizationMembershipListDataProvider::class, 'validationDataCases')]
    public function testListOrganizationMembershipsValidation(array $params, array $expectedErrors): void
    {
        $path = '/api/user/'.$this->user->getId().'/organization-membership?' . http_build_query($params);
        $this->assertPathValidation($this->client, 'GET', $path, [], $expectedErrors);
    }

    #[DataProviderExternal(UserNotFoundDataProvider::class, 'dataCases')]
    public function testNotFoundResponses(string $path, string $method): void
    {
        $this->client->loginUser($this->user, 'api');
        $responseData = $this->getFailureResponseData($this->client, $method, $path, expectedCode: 404);
        $this->assertEquals('User not found', $responseData['message']);
    }

    #[DataProviderExternal(UserAuthDataProvider::class, 'protectedPaths')]
    public function testAuthRequirementForProtectedPaths(string $path, string $method): void
    {
        $this->assertPathIsProtected($path, $method);
    }
}
