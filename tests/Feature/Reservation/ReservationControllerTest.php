<?php

declare(strict_types=1);

namespace App\Tests\Feature\Reservation;

use App\DataFixtures\Test\Availability\AvailabilityFixtures;
use App\DataFixtures\Test\OrganizationMember\OrganizationMemberFixtures;
use App\DataFixtures\Test\Reservation\CancelReservationByUrlConflictFixtures;
use App\DataFixtures\Test\Reservation\CancelReservationByUrlFixtures;
use App\DataFixtures\Test\Reservation\ReservationFixtures;
use App\DataFixtures\Test\Reservation\VerifyReservationConflictFixtures;
use App\DataFixtures\Test\Reservation\VerifyReservationFixtures;
use App\DataFixtures\Test\User\UserFixtures;
use App\Enum\EmailConfirmation\EmailConfirmationType;
use App\Enum\Organization\OrganizationNormalizerGroup;
use App\Enum\Reservation\ReservationNormalizerGroup;
use App\Enum\Schedule\ScheduleNormalizerGroup;
use App\Enum\Service\ServiceNormalizerGroup;
use App\Repository\ReservationRepository;
use App\Repository\ScheduleRepository;
use App\Repository\ServiceRepository;
use App\Repository\UserRepository;
use App\Response\ForbiddenResponse;
use App\Tests\Feature\Reservation\DataProvider\ReservationAuthDataProvider;
use App\Tests\Feature\Reservation\DataProvider\ReservationCancelByUrlDataProvider;
use App\Tests\Feature\Reservation\DataProvider\ReservationConfirmDataProvider;
use App\Tests\Feature\Reservation\DataProvider\ReservationCreateCustomDataProvider;
use App\Tests\Feature\Reservation\DataProvider\ReservationCreateDataProvider;
use App\Tests\Feature\Reservation\DataProvider\ReservationNotFoundDataProvider;
use App\Tests\Feature\Reservation\DataProvider\ReservationOrganizationCancelDataProvider;
use App\Tests\Feature\Reservation\DataProvider\ReservationPatchDataProvider;
use App\Tests\Feature\Reservation\DataProvider\ReservationVerifyDataProvider;
use App\Tests\Feature\Reservation\DataProvider\UserReservationCreateDataProvider;
use App\Tests\Utils\Attribute\Fixtures;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use App\Tests\Utils\BaseWebTestCase;
use App\Tests\Utils\Trait\EmailConfirmationUtils;
use Symfony\Component\Messenger\Transport\InMemory\InMemoryTransport;

class ReservationControllerTest extends BaseWebTestCase
{
    use EmailConfirmationUtils;

    protected InMemoryTransport $mailerTransport;
    protected ServiceRepository $serviceRepository;
    protected ScheduleRepository $scheduleRepository;
    protected ReservationRepository $reservationRepository;
    protected UserRepository $userRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mailerTransport = $this->container->get('messenger.transport.async_mailer');
        $this->serviceRepository = $this->container->get(ServiceRepository::class);
        $this->scheduleRepository = $this->container->get(ScheduleRepository::class);
        $this->reservationRepository = $this->container->get(ReservationRepository::class);
        $this->userRepository = $this->container->get(UserRepository::class);
    }

    #[Fixtures([AvailabilityFixtures::class])]
    #[DataProviderExternal(ReservationCreateDataProvider::class, 'validDataCases')]
    public function testCreate(array $params, array $expectedResponseData): void
    {
        $service = $this->serviceRepository->findOneBy([]);
        $schedule = $this->scheduleRepository->findOneBy([]);
        $params['service_id'] = $service->getId();
        $params['schedule_id'] = $schedule->getId();
        $expectedResponseData['schedule'] = $this->normalizer->normalize($schedule, context: ['groups' => ScheduleNormalizerGroup::BASE_INFO->normalizationGroups()]);
        $expectedResponseData['service'] = $this->normalizer->normalize($service, context: ['groups' => ServiceNormalizerGroup::BASE_INFO->normalizationGroups()]);
        $expectedResponseData['organization'] = $this->normalizer->normalize($schedule->getOrganization(), context: ['groups' => OrganizationNormalizerGroup::BASE_INFO->normalizationGroups()]);

        $responseData = $this->getSuccessfulResponseData($this->client, 'POST', '/api/reservations', $params);
        $this->assertIsInt($responseData['id']);
        $this->assertArrayHasKey('reference', $responseData);
        $this->assertArrayIsEqualToArrayOnlyConsideringListOfKeys($expectedResponseData, $responseData, array_keys($expectedResponseData));
        $this->assertCount(1, $this->mailerTransport->getSent());
    }

    #[DataProviderExternal(ReservationCreateDataProvider::class, 'validationDataCases')]
    public function testCreateValidation(array $params, array $expectedErrors): void
    {
        $this->assertPathValidation($this->client, 'POST', '/api/reservations', $params, $expectedErrors);
        $this->assertCount(0, $this->mailerTransport->getSent());
    }

    #[Fixtures([AvailabilityFixtures::class])]
    #[DataProviderExternal(UserReservationCreateDataProvider::class, 'validDataCases')]
    public function testCreateUserReservation(array $params, array $expectedResponseData): void
    {
        $service = $this->serviceRepository->findOneBy([]);
        $schedule = $this->scheduleRepository->findOneBy([]);
        $params['service_id'] = $service->getId();
        $params['schedule_id'] = $schedule->getId();
        $expectedResponseData['schedule'] = $this->normalizer->normalize($schedule, context: ['groups' => ScheduleNormalizerGroup::BASE_INFO->normalizationGroups()]);
        $expectedResponseData['service'] = $this->normalizer->normalize($service, context: ['groups' => ServiceNormalizerGroup::BASE_INFO->normalizationGroups()]);
        $expectedResponseData['organization'] = $this->normalizer->normalize($schedule->getOrganization(), context: ['groups' => OrganizationNormalizerGroup::BASE_INFO->normalizationGroups()]);

        $this->client->loginUser($this->user, 'api');
        $responseData = $this->getSuccessfulResponseData($this->client, 'POST', '/api/reservations/me', $params);
        $this->assertIsInt($responseData['id']);
        $this->assertArrayHasKey('reference', $responseData);
        $this->assertArrayIsEqualToArrayOnlyConsideringListOfKeys($expectedResponseData, $responseData, array_keys($expectedResponseData));
        $this->assertCount(1, $this->mailerTransport->getSent());
    }

    #[DataProviderExternal(UserReservationCreateDataProvider::class, 'validationDataCases')]
    public function testCreateUserReservationValidation(array $params, array $expectedErrors): void
    {
        $this->client->loginUser($this->user, 'api');
        $this->assertPathValidation($this->client, 'POST', '/api/reservations/me', $params, $expectedErrors);
        $this->assertCount(0, $this->mailerTransport->getSent());
    }

    #[Fixtures([ReservationFixtures::class])]
    #[DataProviderExternal(ReservationCreateCustomDataProvider::class, 'validDataCases')]
    public function testCreateCustom(array $params, array $expectedResponseData): void
    {
        $service = $this->serviceRepository->findOneBy([]);
        $schedule = $this->scheduleRepository->findOneBy([]);
        $params['service_id'] = $service->getId();
        $params['schedule_id'] = $schedule->getId();
        $expectedResponseData['schedule'] = $this->normalizer->normalize($schedule, context: ['groups' => ScheduleNormalizerGroup::BASE_INFO->normalizationGroups()]);
        $expectedResponseData['service'] = $this->normalizer->normalize($service, context: ['groups' => ServiceNormalizerGroup::BASE_INFO->normalizationGroups()]);

        $user = $this->userRepository->findOneBy(['email' => 'sa-user1@example.com']);
        $this->client->loginUser($user, 'api');
        $responseData = $this->getSuccessfulResponseData($this->client, 'POST', '/api/reservations/custom', $params);
        $this->assertIsInt($responseData['id']);
        $this->assertArrayHasKey('reference', $responseData);   
        $this->assertArrayHasKey('reserved_by', $responseData);
        $this->assertArrayHasKey('created_by', $responseData);
        $this->assertArrayHasKey('updated_by', $responseData);
        $this->assertArrayIsEqualToArrayOnlyConsideringListOfKeys($expectedResponseData, $responseData, array_keys($expectedResponseData));
    }

    #[Fixtures([ReservationFixtures::class])]
    #[DataProviderExternal(ReservationCreateCustomDataProvider::class, 'validationDataCases')]
    public function testCreateCustomValidation(array $params, array $expectedErrors): void
    {
        $schedule = $this->scheduleRepository->findOneBy([]);
        $params['schedule_id'] = $schedule->getId();
        $user = $this->userRepository->findOneBy(['email' => 'sa-user1@example.com']);
        $this->client->loginUser($user, 'api');
        $this->assertPathValidation($this->client, 'POST', '/api/reservations/custom', $params, $expectedErrors);
    }

    #[Fixtures([VerifyReservationFixtures::class])]
    public function testVerifySuccess(): void
    {
        $params = $this->prepareEmailConfirmationVerifyParams(EmailConfirmationType::RESERVATION_VERIFICATION);
        $responseData = $this->getSuccessfulResponseData($this->client, 'POST', '/api/reservations/verify', $params);

        $this->assertEquals('Verification Successful', $responseData['message']);
    }

    #[Fixtures([VerifyReservationConflictFixtures::class])]
    public function testVerifyConflict(): void
    {
        $params = $this->prepareEmailConfirmationVerifyParams(EmailConfirmationType::RESERVATION_VERIFICATION);
        $responseData = $this->getFailureResponseData($this->client, 'POST', '/api/reservations/verify', $params, expectedCode: 409);

        $this->assertEquals('Corresponding reservation does not exist, has been cancelled or is already verified.', $responseData['message']);
    }

    #[Fixtures([VerifyReservationFixtures::class])]
    #[DataProviderExternal(ReservationVerifyDataProvider::class, 'failureDataCases')]
    public function testVerifyFailure(array $verifyParams): void
    {
        $validParams = $this->prepareEmailConfirmationVerifyParams(EmailConfirmationType::RESERVATION_VERIFICATION);
        $params = array_merge($validParams, $verifyParams);
        $responseData = $this->getFailureResponseData($this->client, 'POST', '/api/reservations/verify', $params);

        $this->assertEquals('Verification Failed', $responseData['message']);
    }

    #[DataProviderExternal(ReservationVerifyDataProvider::class, 'validationDataCases')]
    public function testVerifyValidation(array $params, array $expectedErrors): void
    {
        $this->assertPathValidation($this->client, 'POST', '/api/reservations/verify', $params, $expectedErrors);
    }

    #[Fixtures([CancelReservationByUrlFixtures::class])]
    public function testCancelByUrlSuccess(): void
    {
        $params = $this->prepareEmailConfirmationVerifyParams(EmailConfirmationType::RESERVATION_CANCELLATION);
        $responseData = $this->getSuccessfulResponseData($this->client, 'POST', '/api/reservations/url-cancel', $params);

        $this->assertEquals('Reservation has been cancelled', $responseData['message']);
    }

    #[Fixtures([CancelReservationByUrlConflictFixtures::class])]
    public function testCancelByUrlConflict(): void
    {
        $params = $this->prepareEmailConfirmationVerifyParams(EmailConfirmationType::RESERVATION_CANCELLATION);
        $responseData = $this->getFailureResponseData($this->client, 'POST', '/api/reservations/url-cancel', $params, expectedCode: 409);

        $this->assertEquals('Corresponding reservation does not exist or already has been cancelled.', $responseData['message']);
    }

    #[Fixtures([CancelReservationByUrlFixtures::class])]
    #[DataProviderExternal(ReservationCancelByUrlDataProvider::class, 'failureDataCases')]
    public function testCancelByUrlFailure(array $verifyParams): void
    {
        $validParams = $this->prepareEmailConfirmationVerifyParams(EmailConfirmationType::RESERVATION_CANCELLATION);
        $params = array_merge($validParams, $verifyParams);
        $responseData = $this->getFailureResponseData($this->client, 'POST', '/api/reservations/url-cancel', $params);

        $this->assertEquals('Verification Failed', $responseData['message']);
    }

    #[DataProviderExternal(ReservationCancelByUrlDataProvider::class, 'validationDataCases')]
    public function testCancelByUrlValidation(array $params, array $expectedErrors): void
    {
        $this->assertPathValidation($this->client, 'POST', '/api/reservations/url-cancel', $params, $expectedErrors);
    }

    #[Fixtures([ReservationFixtures::class])]
    #[DataProviderExternal(ReservationOrganizationCancelDataProvider::class, 'dataCases')]
    public function testOrganizationCancel(array $params, bool $emailSent): void
    {
        $reservationId = $this->reservationRepository->findOneBy([])->getId();
        $user = $this->userRepository->findOneBy(['email' => 'sa-user1@example.com']);
        $this->client->loginUser($user, 'api');
        $responseData = $this->getSuccessfulResponseData($this->client, 'POST', "/api/reservations/$reservationId/organization-cancel", $params);
        $this->assertEquals('Reservation has been cancelled', $responseData['message']);
        $this->assertCount($emailSent ? 1 : 0, $this->mailerTransport->getSent());
    }

    #[Fixtures([ReservationFixtures::class])]
    #[DataProviderExternal(ReservationConfirmDataProvider::class, 'validDataCases')]
    public function testConfirm(array $params): void
    {
        $reservationId = $this->reservationRepository->findOneBy([])->getId();
        $user = $this->userRepository->findOneBy(['email' => 'sa-user1@example.com']);
        $this->client->loginUser($user, 'api');
        $responseData = $this->getSuccessfulResponseData($this->client, 'POST', "/api/reservations/$reservationId/confirm", $params);
        $this->assertEquals(['message' => 'Reservation has been confirmed'], $responseData);
        $this->assertCount(1, $this->mailerTransport->getSent());
    }

    #[Fixtures([ReservationFixtures::class])]
    #[DataProviderExternal(ReservationConfirmDataProvider::class, 'validationDataCases')]
    public function testConfirmValidation(array $params, array $expectedErrors): void
    {
        $reservationId = $this->reservationRepository->findOneBy([])->getId();
        $this->client->loginUser($this->user, 'api');
        $this->assertPathValidation($this->client, 'POST', "/api/reservations/$reservationId/confirm", $params, $expectedErrors);
        $this->assertCount(0, $this->mailerTransport->getSent());
    }

    #[Fixtures([ReservationFixtures::class])]
    public function testGet(): void
    {
        $reservation = $this->reservationRepository->findOneBy([]);
        $reservationId = $reservation->getId();
        $expectedResponseData = $this->normalize($reservation, ReservationNormalizerGroup::ORGANIZATION_RESERVATIONS->normalizationGroups());
        $user = $this->userRepository->findOneBy(['email' => 'sa-user2@example.com']);
        $this->client->loginUser($user, 'api');
        $responseData = $this->getSuccessfulResponseData($this->client, 'GET', "/api/reservations/$reservationId");

        $this->assertEquals($expectedResponseData, $responseData);
    }

    #[Fixtures([ReservationFixtures::class])]
    #[DataProviderExternal(ReservationPatchDataProvider::class, 'validDataCases')]
    public function testPatch(array $params, array $expectedResponseData, bool $emailSent): void
    {
        $reservation = $this->reservationRepository->findOneBy([]);
        $reservationId = $reservation->getId();
        $service = $this->serviceRepository->findOneBy([]);
        $schedule = $this->scheduleRepository->findOneBy([]);
        $params['service_id'] = $service->getId();
        $params['schedule_id'] = $schedule->getId();
        $expectedResponseData['schedule'] = $this->normalizer->normalize($schedule, context: ['groups' => ScheduleNormalizerGroup::BASE_INFO->normalizationGroups()]);
        $expectedResponseData['service'] = $this->normalizer->normalize($service, context: ['groups' => ServiceNormalizerGroup::BASE_INFO->normalizationGroups()]);

        $user = $this->userRepository->findOneBy(['email' => 'sa-user1@example.com']);
        $this->client->loginUser($user, 'api');

        $responseData = $this->getSuccessfulResponseData($this->client, 'PATCH', "/api/reservations/$reservationId", $params);
        $this->assertIsInt($responseData['id']);
        $this->assertArrayHasKey('reference', $responseData);
        $this->assertArrayHasKey('reserved_by', $responseData);
        $this->assertArrayHasKey('created_by', $responseData);
        $this->assertArrayHasKey('updated_by', $responseData);
        $this->assertArrayIsEqualToArrayOnlyConsideringListOfKeys($expectedResponseData, $responseData, array_keys($expectedResponseData));
        $this->assertCount($emailSent ? 1 : 0, $this->mailerTransport->getSent());
    }

    #[Fixtures([ReservationFixtures::class])]
    #[DataProviderExternal(ReservationPatchDataProvider::class, 'validationDataCases')]
    public function testPatchValidation(array $params, array $expectedErrors): void
    {
        $reservation = $this->reservationRepository->findOneBy([]);
        $reservationId = $reservation->getId();
        $schedule = $this->scheduleRepository->findOneBy([]);
        $params['schedule_id'] = $schedule->getId();
        
        $this->client->loginUser($this->user, 'api');
        $this->assertPathValidation($this->client, 'PATCH', "/api/reservations/$reservationId", $params, $expectedErrors);
        $this->assertCount(0, $this->mailerTransport->getSent());
    }

    #[DataProviderExternal(ReservationNotFoundDataProvider::class, 'dataCases')]
    public function testNotFoundResponses(string $path, string $method): void
    {
        $this->client->loginUser($this->user, 'api');
        $responseData = $this->getFailureResponseData($this->client, $method, $path, expectedCode: 404);
        $this->assertEquals('Reservation not found', $responseData['message']);
    }

    #[Fixtures([ReservationFixtures::class])]
    #[DataProviderExternal(ReservationAuthDataProvider::class, 'protectedPaths')]
    public function testAuthRequirementForProtectedPaths(string $path, string $method): void
    {
        $reservationId = $this->reservationRepository->findOneBy([])->getId();
        $path = str_replace('{reservation}', (string)$reservationId, $path);
        $this->assertPathIsProtected($path, $method);
    }

    #[Fixtures([UserFixtures::class, OrganizationMemberFixtures::class, ReservationFixtures::class])]
    #[DataProviderExternal(ReservationAuthDataProvider::class, 'privilegesOnlyPaths')]
    public function testPrivilegesRequirementForProtectedPaths(string $path, string $method, string $userEmail, array $parameters = []): void
    {
        $reservationId = $this->reservationRepository->findOneBy([])->getId();
        $scheduleId = $this->scheduleRepository->findOneBy([])->getId();
        $path = str_replace('{reservation}', (string)$reservationId, $path);
        $parameters = array_map(fn($val) => $val == '{schedule}' ? $scheduleId : $val, $parameters);
        $user = $this->userRepository->findOneBy(['email' => $userEmail]);

        $this->client->loginUser($user, 'api');
        $responseData = $this->getFailureResponseData($this->client, $method, $path, expectedCode: 403);
        $this->assertEquals(ForbiddenResponse::RESPONSE_MESSAGE, $responseData['message']);
    }
}
