<?php

declare(strict_types=1);

namespace App\Tests\Feature\Reservation;

use App\DataFixtures\Test\Reservation\CancelReservationByUrlConflictFixtures;
use App\DataFixtures\Test\Reservation\CancelReservationByUrlFixtures;
use App\DataFixtures\Test\Reservation\VerifyReservationConflictFixtures;
use App\DataFixtures\Test\Reservation\VerifyReservationFixtures;
use App\Enum\EmailConfirmation\EmailConfirmationType;
use App\Repository\ReservationRepository;
use App\Tests\Feature\Reservation\DataProvider\ReservationCancelByUrlDataProvider;
use App\Tests\Feature\Reservation\DataProvider\ReservationVerifyDataProvider;
use App\Tests\Utils\Attribute\Fixtures;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use App\Tests\Utils\BaseWebTestCase;
use App\Tests\Utils\Trait\EmailConfirmationUtils;
use Symfony\Component\Messenger\Transport\InMemory\InMemoryTransport;

class ScheduleReservationControllerTest extends BaseWebTestCase
{
    use EmailConfirmationUtils;

    protected InMemoryTransport $mailerTransport;
    protected ReservationRepository $reservationRepository;


    protected function setUp(): void
    {
        parent::setUp();
        $this->mailerTransport = $this->container->get('messenger.transport.async_mailer');
        $this->reservationRepository = $this->container->get(ReservationRepository::class);
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
}
