<?php

declare(strict_types=1);

namespace App\Tests\Service\Entity;

use App\DTO\Reservation\ReservationCreateDTO;
use App\DTO\UserReservation\UserReservationCreateDTO;
use App\Entity\Reservation;
use App\Entity\User;
use App\Enum\EmailType;
use App\Enum\Reservation\ReservationStatus;
use App\Exceptions\ConflictException;
use App\Repository\ReservationRepository;
use App\Service\Entity\ReservationService;
use App\Service\Entity\UserReservationService;
use App\Tests\Unit\Service\Entity\DataProvider\UserReservationServiceDataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProviderExternal;

class UserReservationServiceTest extends TestCase
{
    private ReservationRepository&MockObject $reservationRepositoryMock;
    private ReservationService&MockObject $reservationServiceMock;
    private UserReservationService $service;

    protected function setUp(): void
    {
        $this->reservationRepositoryMock = $this->createMock(ReservationRepository::class);
        $this->reservationServiceMock = $this->createMock(ReservationService::class);

        $this->service = new UserReservationService(
            $this->reservationRepositoryMock,
            $this->reservationServiceMock,
        );
    }

    public function testCreateUserReservationSuccess(): void
    {
        $dto = new UserReservationCreateDTO(1, 2, '+48213721372', '2025-10-20T10:00+00:00', 'test');
        $userEmail = 'user@example.com';
        $languagePreference = 'en';
        $userMock = $this->createMock(User::class);
        $userMock->method('getEmail')->willReturn($userEmail);
        $userMock->method('getLanguagePreference')->willReturn($languagePreference);
        $reservationMock = $this->createMock(Reservation::class);

        $reservationMock->expects($this->once())->method('setVerified')->with(true);
        $reservationMock->expects($this->once())->method('setReservedBy')->with($userMock);

        $this->reservationServiceMock
            ->method('makeReservation')
            ->with(new ReservationCreateDTO(
                $dto->scheduleId,
                $dto->serviceId,
                $userEmail,
                $dto->phoneNumber,
                $dto->startDateTime,
                $languagePreference
            ))
            ->willReturn($reservationMock);

        $this->reservationServiceMock
            ->expects($this->once())
            ->method('validateReservationAvailability')
            ->with($reservationMock);

        $this->reservationRepositoryMock
            ->expects($this->once())
            ->method('save')
            ->with($reservationMock, true);

        $this->reservationServiceMock
            ->expects($this->once())
            ->method('sendReservationNotification')
            ->with($reservationMock, $dto->verificationHandler, EmailType::RESERVATION_SUMMARY);

        $result = $this->service->createUserReservation($dto, $userMock);
        $this->assertSame($reservationMock, $result);
    }

    public function testCreateUserReservationThrowsConflictWhenUnavailableTimeSlot(): void
    {
        $dto = new UserReservationCreateDTO(1, 2, '+48213721372', '2025-10-20T10:00+00:00', 'test');
        $userEmail = 'user@example.com';
        $languagePreference = 'en';
        $userMock = $this->createMock(User::class);
        $userMock->method('getEmail')->willReturn($userEmail);
        $userMock->method('getLanguagePreference')->willReturn($languagePreference);
        $reservationMock = $this->createMock(Reservation::class);

        $reservationMock->expects($this->never())->method('setVerified');
        $reservationMock->expects($this->never())->method('setReservedBy');

        $this->reservationServiceMock
            ->method('makeReservation')
            ->with(new ReservationCreateDTO(
                $dto->scheduleId,
                $dto->serviceId,
                $userEmail,
                $dto->phoneNumber,
                $dto->startDateTime,
                $languagePreference
            ))
            ->willReturn($reservationMock);

        $this->reservationServiceMock
            ->expects($this->once())
            ->method('validateReservationAvailability')
            ->with($reservationMock)
            ->willThrowException(new ConflictException());

        $this->reservationRepositoryMock
            ->expects($this->never())
            ->method('save');

        $this->reservationServiceMock
            ->expects($this->never())
            ->method('sendReservationNotification');

        $this->expectException(ConflictException::class);
        $this->service->createUserReservation($dto, $userMock);
    }

    public function testCancelUserReservationSuccess(): void
    {
        $reservationMock = $this->createMock(Reservation::class);
        $reservationMock->method('getStatus')->willReturn(ReservationStatus::PENDING->value);
        $userMock  = $this->createMock(User::class);
        $userMock->method('hasReservation')->with($reservationMock)->willReturn(true);

        $reservationMock->expects($this->once())->method('setStatus')->with(ReservationStatus::CUSTOMER_CANCELLED->value);
        $this->reservationRepositoryMock->expects($this->once())->method('save')->with($reservationMock, true);

        $this->reservationServiceMock
            ->expects($this->once())
            ->method('sendReservationCancelledNotification');

        $this->service->cancelUserReservation($reservationMock, $userMock);
    }

    #[DataProviderExternal(UserReservationServiceDataProvider::class, 'cancelUserReservationExceptionDataCases')]
    public function testCancelUserReservationThrowsException(ReservationStatus $reservationStatus, bool $hasReservation, string $expectedException): void
    {
        $reservationMock = $this->createMock(Reservation::class);
        $reservationMock->method('getStatus')->willReturn($reservationStatus->value);
        $userMock = $this->createMock(User::class);
        $userMock->method('hasReservation')->with($reservationMock)->willReturn($hasReservation);

        $this->expectException($expectedException);

        $this->service->cancelUserReservation($reservationMock, $userMock);
    }
}
