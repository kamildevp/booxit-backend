<?php

declare(strict_types=1);

namespace App\Tests\Service\Entity;

use App\DTO\Reservation\ReservationCreateDTO;
use App\DTO\ScheduleReservation\ScheduleReservationConfirmDTO;
use App\DTO\ScheduleReservation\ScheduleReservationCreateCustomDTO;
use App\DTO\ScheduleReservation\ScheduleReservationCreateDTO;
use App\DTO\ScheduleReservation\ScheduleReservationPatchDTO;
use App\Entity\Reservation;
use App\Entity\Organization;
use App\Entity\Schedule;
use App\Enum\EmailType;
use App\Enum\Reservation\ReservationStatus;
use App\Enum\Reservation\ReservationType;
use App\Exceptions\ConflictException;
use App\Repository\ReservationRepository;
use App\Service\Entity\ReservationService;
use App\Service\EntitySerializer\EntitySerializerInterface;
use App\Service\Entity\ScheduleReservationService;
use App\Tests\Unit\Service\Entity\DataProvider\ScheduleReservationServiceDataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use DateTimeImmutable;
use DateTimeInterface;
use PHPUnit\Framework\Attributes\DataProviderExternal;

class ScheduleReservationServiceTest extends TestCase
{
    private EntitySerializerInterface&MockObject $entitySerializerMock;
    private ReservationRepository&MockObject $reservationRepositoryMock;
    private ReservationService&MockObject $reservationServiceMock;
    private ScheduleReservationService $service;

    protected function setUp(): void
    {
        $this->entitySerializerMock = $this->createMock(EntitySerializerInterface::class);
        $this->reservationRepositoryMock = $this->createMock(ReservationRepository::class);
        $this->reservationServiceMock = $this->createMock(ReservationService::class);

        $this->service = new ScheduleReservationService(
            $this->entitySerializerMock,
            $this->reservationRepositoryMock,
            $this->reservationServiceMock,
        );
    }

    public function testCreateScheduleReservationSuccess(): void
    {
        $dto = new ScheduleReservationCreateDTO(2, 'user@example.com', '+48213721372', '2025-10-20T10:00+00:00', 'test', 'en');
        $scheduleId = 1;
        $scheduleMock = $this->createMock(Schedule::class);
        $scheduleMock->method('getId')->willReturn($scheduleId);
        $reservationMock = $this->createMock(Reservation::class);

        $this->reservationServiceMock
            ->method('makeReservation')
            ->with(new ReservationCreateDTO(
                $scheduleId,
                $dto->serviceId,
                $dto->email,
                $dto->phoneNumber,
                $dto->startDateTime,
                $dto->languagePreference
            ))
            ->willReturn($reservationMock);

        $this->reservationServiceMock
            ->expects($this->once())
            ->method('validateReservationAvailability')
            ->with($reservationMock);

        $reservationMock->expects($this->once())->method('setExpiryDate')->with($this->isInstanceOf(DateTimeInterface::class));

        $this->reservationRepositoryMock
            ->expects($this->once())
            ->method('save')
            ->with($reservationMock, true);

        $this->reservationServiceMock
            ->expects($this->once())
            ->method('sendReservationVerification')
            ->with($reservationMock, $dto->verificationHandler);

        $result = $this->service->createScheduleReservation($scheduleMock, $dto);
        $this->assertSame($reservationMock, $result);
    }

    public function testCreateReservationThrowsConflictWhenUnavailableTimeSlot(): void
    {
        $dto = new ScheduleReservationCreateDTO(2, 'user@example.com', '+48213721372', '2025-10-20T10:00+00:00', 'test', 'en');
        $scheduleId = 1;
        $scheduleMock = $this->createMock(Schedule::class);
        $scheduleMock->method('getId')->willReturn($scheduleId);
        $reservationMock = $this->createMock(Reservation::class);

        $this->reservationServiceMock
            ->method('makeReservation')
            ->with(new ReservationCreateDTO(
                $scheduleId,
                $dto->serviceId,
                $dto->email,
                $dto->phoneNumber,
                $dto->startDateTime,
                $dto->languagePreference
            ))
            ->willReturn($reservationMock);

        $this->reservationServiceMock
            ->method('validateReservationAvailability')
            ->with($reservationMock)
            ->willThrowException(new ConflictException());

        $reservationMock->expects($this->never())->method('setExpiryDate');

        $this->reservationRepositoryMock
            ->expects($this->never())
            ->method('save');

        $this->reservationServiceMock
            ->expects($this->never())
            ->method('sendReservationVerification');

        $this->expectException(ConflictException::class);
        $this->service->createScheduleReservation($scheduleMock, $dto);
    }

    public function testCreateCustomReservation(): void
    {
        $dto = new ScheduleReservationCreateCustomDTO(2, '+48213721372', 'user@example.com', '25.50', '2025-10-20T10:00+00:00', '2025-10-20T11:00+00:00', ReservationStatus::CONFIRMED->value, 'en');
        $ref = 'ref';
        $organizationMock = $this->createMock(Organization::class);
        $scheduleMock = $this->createMock(Schedule::class);
        $scheduleMock->method('getOrganization')->willReturn($organizationMock);
        $reservationMock = $this->createMock(Reservation::class);
        $reservationMock->method('getSchedule')->willReturn($scheduleMock);
        $reservationMock->method('getStartDateTime')->willReturn(new DateTimeImmutable());

        $this->entitySerializerMock
            ->method('parseToEntity')
            ->with($dto, Reservation::class)
            ->willReturn($reservationMock);

        $this->reservationServiceMock
            ->method('generateReservationReference')
            ->with($reservationMock)
            ->willReturn($ref);

        $reservationMock->expects($this->once())->method('setReference')->with($ref);
        $reservationMock->expects($this->once())->method('setSchedule')->with($scheduleMock);
        $reservationMock->expects($this->once())->method('setOrganization')->with($organizationMock);
        $reservationMock->expects($this->once())->method('setType')->with(ReservationType::CUSTOM->value);
        $reservationMock->expects($this->once())->method('setVerified')->with(true);

        $this->reservationRepositoryMock
            ->expects($this->once())
            ->method('save')
            ->with($reservationMock, true);

        $result = $this->service->createCustomScheduleReservation($scheduleMock, $dto);
        $this->assertSame($reservationMock, $result);
    }

    public function testCancelScheduleReservation(): void
    {
        $reservationMock = $this->createMock(Reservation::class);
        $reservationMock->method('getStatus')->willReturn(ReservationStatus::PENDING->value);

        $reservationMock->expects($this->once())->method('setStatus')->with(ReservationStatus::ORGANIZATION_CANCELLED->value);
        $this->reservationRepositoryMock
            ->expects($this->once())
            ->method('save')
            ->with($reservationMock, true);

        $this->reservationServiceMock
            ->expects($this->once())
            ->method('sendReservationCancelledNotification')
            ->with($reservationMock);

        $this->service->cancelScheduleReservation($reservationMock);
    }

    #[DataProviderExternal(ScheduleReservationServiceDataProvider::class, 'cancelScheduleReservationConflictDataCases')]
    public function testCancelScheduleReservationThrowsConflict(ReservationStatus $reservationStatus): void
    {
        $reservationMock = $this->createMock(Reservation::class);
        $reservationMock->method('getStatus')->willReturn($reservationStatus->value);

        $reservationMock->expects($this->never())->method('setStatus');
        $this->reservationRepositoryMock
            ->expects($this->never())
            ->method('save');

        $this->reservationServiceMock
            ->expects($this->never())
            ->method('sendReservationCancelledNotification');

        $this->expectException(ConflictException::class);

        $this->service->cancelScheduleReservation($reservationMock);
    }

    public function testConfirmScheduleReservation(): void
    {
        $dto = new ScheduleReservationConfirmDTO('test');
        $reservationMock = $this->createMock(Reservation::class);
        $reservationMock->method('getStatus')->willReturn(ReservationStatus::PENDING->value);

        $reservationMock->expects($this->once())->method('setStatus')->with(ReservationStatus::CONFIRMED->value);

        $this->reservationRepositoryMock
            ->expects($this->once())
            ->method('save')
            ->with($reservationMock, true);

        $this->reservationServiceMock
            ->expects($this->once())
            ->method('sendReservationNotification')
            ->with($reservationMock, $dto->verificationHandler, EmailType::RESERVATION_CONFIRMATION);

        $this->service->confirmScheduleReservation($reservationMock, $dto);
    }

    public function testConfirmReservationConflict(): void
    {
        $dto = new ScheduleReservationConfirmDTO('test');
        $reservationMock = $this->createMock(Reservation::class);
        $reservationMock->method('getStatus')->willReturn(ReservationStatus::CONFIRMED->value);

        $reservationMock->expects($this->never())->method('setStatus')->with(ReservationStatus::CONFIRMED->value);

        $this->reservationRepositoryMock
            ->expects($this->never())
            ->method('save');

        $this->reservationServiceMock
            ->expects($this->never())
            ->method('sendReservationNotification');

        $this->expectException(ConflictException::class);

        $this->service->confirmScheduleReservation($reservationMock, $dto);
    }

    #[DataProviderExternal(ScheduleReservationServiceDataProvider::class, 'patchScheduleReservationDataCases')]
    public function testPatchReservation(bool $notifyCustomer): void
    {
        $dto = new ScheduleReservationPatchDTO(2, '+48213721372', 'user@example.com', '25.50', '2025-10-20T10:00+00:00', '2025-10-20T11:00+00:00', ReservationStatus::CONFIRMED->value, $notifyCustomer, 'test');
        $reservationMock = $this->createMock(Reservation::class);

        $this->entitySerializerMock
            ->method('parseToEntity')
            ->with($dto, $reservationMock)
            ->willReturn($reservationMock);

        $this->reservationRepositoryMock
            ->expects($this->once())
            ->method('save')
            ->with($reservationMock, true);

        $this->reservationServiceMock
            ->expects($notifyCustomer ? $this->once() : $this->never())
            ->method('sendReservationNotification')
            ->with($reservationMock, $dto->verificationHandler, EmailType::RESERVATION_UPDATED_NOTIFICATION);

        $result = $this->service->patchScheduleReservation($reservationMock, $dto);
        $this->assertSame($reservationMock, $result);
    }
}
