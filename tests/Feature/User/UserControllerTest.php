<?php

declare(strict_types=1);

namespace App\Tests\Feature\User;

use App\DataFixtures\Test\User\ChangeUserPasswordFixtures;
use App\DataFixtures\Test\User\PasswordResetFixtures;
use App\DataFixtures\Test\User\UserFixtures;
use App\DataFixtures\Test\User\UserSortingFixtures;
use App\DataFixtures\Test\User\VerifyUserFixtures;
use App\Entity\User;
use App\Enum\EmailConfirmationType;
use App\Enum\User\UserNormalizerGroup;
use App\Repository\UserRepository;
use App\Tests\Feature\Attribute\Fixtures;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use App\Tests\Feature\BaseWebTestCase;
use App\Tests\Feature\Trait\EmailConfirmationUtils;
use App\Tests\Feature\User\DataProvider\UserAuthDataProvider;
use App\Tests\Feature\User\DataProvider\UserChangePasswordDataProvider;
use App\Tests\Feature\User\DataProvider\UserCreateDataProvider;
use App\Tests\Feature\User\DataProvider\UserListDataProvider;
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

    protected function setUp(): void
    {
        parent::setUp();
        $this->mailerTransport = $this->container->get('messenger.transport.async_mailer');
        $this->userRepository = $this->container->get(EntityManagerInterface::class)->getRepository(User::class);
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

    public function testGetForNotExistingUser(): void
    {
        $responseData = $this->getFailureResponseData($this->client, 'GET', "/api/user/1000", expectedCode: 404);
        $this->assertEquals('User not found', $responseData['message']);
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
        $path = '/api/user?' . http_build_query($filters);
        $responseData = $this->getSuccessfulResponseData($this->client, 'GET', $path);

        $this->assertCount(1, $responseData['items']);
        $this->assertArrayIsEqualToArrayOnlyConsideringListOfKeys($expectedItemData, $responseData['items'][0], array_keys($expectedItemData));
    }

    #[Fixtures([UserSortingFixtures::class], false)]
    #[DataProviderExternal(UserListDataProvider::class, 'sortingDataCases')]
    public function testListSorting(array $sorting, array $orderedItems): void
    {
        $path = '/api/user?' . http_build_query($sorting);
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

    #[DataProviderExternal(UserAuthDataProvider::class, 'protectedPaths')]
    public function testAuthRequirementForProtectedPaths(string $path, string $method): void
    {
        $this->assertPathIsProtected($path, $method);
    }
}
