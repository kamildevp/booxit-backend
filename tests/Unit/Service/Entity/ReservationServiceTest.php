<?php

declare(strict_types=1);

namespace App\Tests\Service\Entity;

use App\DTO\Reservation\ReservationCreateDTO;
use App\Entity\Reservation;
use App\Entity\EmailConfirmation;
use App\Entity\Organization;
use App\Entity\Schedule;
use App\Entity\Service;
use App\Enum\EmailConfirmation\EmailConfirmationType;
use App\Enum\EmailType;
use App\Enum\Reservation\ReservationStatus;
use App\Enum\Reservation\ReservationType;
use App\Exceptions\ConflictException;
use App\Message\ReservationVerificationMessage;
use App\Repository\ReservationRepository;
use App\Service\Entity\ReservationService;
use App\Service\EntitySerializer\EntitySerializerInterface;
use App\Service\Entity\EmailConfirmationService;
use App\Service\EmailConfirmation\EmailConfirmationHandlerInterface;
use App\Service\Entity\AvailabilityService;
use DateInterval;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\MessageBusInterface;
use DateTimeImmutable;
use DateTimeInterface;
use Symfony\Component\Messenger\Envelope;

class ReservationServiceTest extends TestCase
{
    private EntitySerializerInterface&MockObject $entitySerializerMock;
    private AvailabilityService&MockObject $availabilityServiceMock;
    private EmailConfirmationService&MockObject $emailConfirmationServiceMock;
    private ReservationRepository&MockObject $reservationRepositoryMock;
    private EmailConfirmationHandlerInterface&MockObject $emailConfirmationHandlerMock;
    private MessageBusInterface&MockObject $messageBusMock;
    private ReservationService $service;

    protected function setUp(): void
    {
        $this->entitySerializerMock = $this->createMock(EntitySerializerInterface::class);
        $this->availabilityServiceMock = $this->createMock(AvailabilityService::class);
        $this->emailConfirmationServiceMock = $this->createMock(EmailConfirmationService::class);
        $this->reservationRepositoryMock = $this->createMock(ReservationRepository::class);
        $this->emailConfirmationHandlerMock = $this->createMock(EmailConfirmationHandlerInterface::class);
        $this->messageBusMock = $this->createMock(MessageBusInterface::class);

        $this->service = new ReservationService(
            $this->entitySerializerMock,
            $this->availabilityServiceMock,
            $this->emailConfirmationServiceMock,
            $this->reservationRepositoryMock,
            $this->emailConfirmationHandlerMock,
            $this->messageBusMock,
        );
    }

    public function testCreateReservationSuccess(): void
    {
        $dto = new ReservationCreateDTO(1, 2, 'user@example.com', '+48213721372', '2025-10-20T10:00+00:00', 'test');
        $reservationId = 3;
        $startDateTime = new DateTimeImmutable('2025-10-20 10:00');
        $endDateTime = new DateTimeImmutable('2025-10-20 11:00');
        $duration = new DateInterval('PT1H');
        $data = [
            'reference' => 'ref',
            'verification_url' => 'api/reservation/verify',
            'verification_expiration_date' => new DateTimeImmutable('+ 30 minutes'),
            'cancellation_url' => 'api/reservation/cancel',
            'cancellation_expiration_date' => $startDateTime,
            'organization_name' => 'org_name',
            'service_name' => 'srv_name',
            'start_date_time' => $startDateTime,
            'estimated_price' => '25.50',
            'duration' => $duration->format('%h:%ih'),
        ];

        $reservationMock = $this->createMock(Reservation::class);
        $serviceMock = $this->createMock(Service::class);
        $scheduleMock = $this->createMock(Schedule::class);
        $organizationMock = $this->createMock(Organization::class);

        $this->entitySerializerMock
            ->method('parseToEntity')
            ->with($dto, Reservation::class)
            ->willReturn($reservationMock);

        $serviceMock->method('getId')->willReturn($dto->serviceId);
        $serviceMock->method('getDuration')->willReturn($duration);
        $serviceMock->method('getEstimatedPrice')->willReturn($data['estimated_price']);
        $serviceMock->method('getName')->willReturn($data['service_name']);

        $scheduleMock->method('getId')->willReturn($dto->scheduleId);
        $scheduleMock->method('getOrganization')->willReturn($organizationMock);

        $organizationMock->method('getName')->willReturn($data['organization_name']);

        $reservationMock->method('getId')->willReturn($reservationId);
        $reservationMock->method('getReference')->willReturn($data['reference']);
        $reservationMock->method('getOrganization')->willReturn($organizationMock);
        $reservationMock->method('getService')->willReturn($serviceMock);
        $reservationMock->method('getSchedule')->willReturn($scheduleMock);
        $reservationMock->method('getStartDateTime')->willReturn($startDateTime);
        $reservationMock->method('getEndDateTime')->willReturn($endDateTime);
        $reservationMock->method('getEmail')->willReturn($dto->email);
        $reservationMock->method('getExpiryDate')->willReturn($data['verification_expiration_date']);
        $reservationMock->method('getEstimatedPrice')->willReturn($data['estimated_price']);

        $reservationMock->expects($this->once())->method('setEndDateTime')->with($endDateTime);
        $reservationMock->expects($this->once())->method('setOrganization')->with($organizationMock);
        $reservationMock->expects($this->once())->method('setEstimatedPrice')->with($data['estimated_price']);
        $reservationMock->expects($this->once())->method('setStatus')->with(ReservationStatus::PENDING->value);
        $reservationMock->expects($this->once())->method('setType')->with(ReservationType::REGULAR->value);
        $reservationMock->expects($this->once())->method('setVerified')->with(false);
        $reservationMock->expects($this->once())->method('setExpiryDate')->with($this->isInstanceOf(DateTimeInterface::class));

        $this->availabilityServiceMock
            ->method('getScheduleAvailability')
            ->with($scheduleMock, $serviceMock, $startDateTime, $endDateTime)
            ->willReturn([
                $startDateTime->format('Y-m-d') => [$startDateTime->format('H:i')]
            ]);

        $this->reservationRepositoryMock
            ->expects($this->once())
            ->method('save')
            ->with($reservationMock, true);

        $verificationEmailConfirmationId = 123;
        $verificationEmailConfirmationMock = $this->createMock(EmailConfirmation::class);
        $verificationEmailConfirmationMock->method('getId')->willReturn($verificationEmailConfirmationId);
        $verificationEmailConfirmationMock->method('getExpiryDate')->willReturn($data['verification_expiration_date']);

        $cancellationEmailConfirmationId = 124;
        $cancellationEmailConfirmationMock = $this->createMock(EmailConfirmation::class);
        $cancellationEmailConfirmationMock->method('getId')->willReturn($cancellationEmailConfirmationId);
        $cancellationEmailConfirmationMock->method('getExpiryDate')->willReturn($data['cancellation_expiration_date']);

        $this->emailConfirmationServiceMock
            ->method('createEmailConfirmation')
            ->willReturnMap([
                [
                    $dto->email, 
                    $dto->verificationHandler, 
                    EmailConfirmationType::RESERVATION_VERIFICATION->value, 
                    null, 
                    $data['verification_expiration_date'],
                    ['reservation_id' => $reservationId],
                    $verificationEmailConfirmationMock
                ],
                [
                    $dto->email, 
                    $dto->verificationHandler, 
                    EmailConfirmationType::RESERVATION_CANCELLATION->value, 
                    null, 
                    $data['cancellation_expiration_date'],
                    ['reservation_id' => $reservationId],
                    $cancellationEmailConfirmationMock
                ]
            ]);

        $this->emailConfirmationHandlerMock
            ->method('generateSignedUrl')
            ->willReturnMap([
                [$verificationEmailConfirmationMock, $data['verification_url']],
                [$cancellationEmailConfirmationMock, $data['cancellation_url']],
            ]);

        $this->messageBusMock
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(fn($arg) => 
                $arg instanceof ReservationVerificationMessage &&
                $arg->getEmailConfirmationId() == $verificationEmailConfirmationId &&
                $arg->getReservationId() == $reservationId &&
                $arg->getEmailType() == EmailType::RESERVATION_VERIFICATION->value && 
                $arg->getEmail() == $dto->email && 
                $arg->getTemplateParams() == $data
            ))
            ->willReturn(new Envelope($this->createMock(ReservationVerificationMessage::class)));

        $result = $this->service->createReservation($dto);
        
        $this->assertSame($reservationMock, $result);
    }

    public function testCreateReservationThrowsConflictWhenUnavailableTimeSlot(): void
    {
        $dto = new ReservationCreateDTO(1, 2, 'user@example.com', '+48213721372', '2025-10-20T10:00+00:00', 'test');
        $startDateTime = new DateTimeImmutable('2025-10-20 10:00');
        $endDateTime = new DateTimeImmutable('2025-10-20 11:00');
        $duration = new DateInterval('PT1H');

        $reservationMock = $this->createMock(Reservation::class);
        $serviceMock = $this->createMock(Service::class);
        $scheduleMock = $this->createMock(Schedule::class);
        $organizationMock = $this->createMock(Organization::class);

        $this->entitySerializerMock
            ->method('parseToEntity')
            ->with($dto, Reservation::class)
            ->willReturn($reservationMock);

        $serviceMock->method('getId')->willReturn($dto->serviceId);
        $serviceMock->method('getDuration')->willReturn($duration);
        $serviceMock->method('getEstimatedPrice')->willReturn('25.50');

        $scheduleMock->method('getId')->willReturn($dto->scheduleId);
        $scheduleMock->method('getOrganization')->willReturn($organizationMock);

        $reservationMock->method('getService')->willReturn($serviceMock);
        $reservationMock->method('getSchedule')->willReturn($scheduleMock);
        $reservationMock->method('getStartDateTime')->willReturn($startDateTime);
        $reservationMock->method('getEndDateTime')->willReturn($endDateTime);

        $this->availabilityServiceMock
            ->method('getScheduleAvailability')
            ->with($scheduleMock, $serviceMock, $startDateTime, $endDateTime)
            ->willReturn([
                $startDateTime->format('Y-m-d') => []
            ]);

        $this->reservationRepositoryMock
            ->expects($this->never())
            ->method('save');

        $this->messageBusMock
            ->expects($this->never())
            ->method('dispatch');

        $this->expectException(ConflictException::class);

        $this->service->createReservation($dto);
    }
}
