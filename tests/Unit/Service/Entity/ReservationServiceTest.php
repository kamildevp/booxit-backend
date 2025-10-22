<?php

declare(strict_types=1);

namespace App\Tests\Service\Entity;

use App\DTO\Reservation\ReservationConfirmDTO;
use App\DTO\Reservation\ReservationCreateDTO;
use App\DTO\Reservation\ReservationOrganizationCancelDTO;
use App\DTO\Reservation\ReservationPatchDTO;
use App\DTO\Reservation\ReservationUrlCancelDTO;
use App\DTO\Reservation\ReservationVerifyDTO;
use App\DTO\Reservation\UserReservationCreateDTO;
use App\Entity\Reservation;
use App\Entity\EmailConfirmation;
use App\Entity\Organization;
use App\Entity\Schedule;
use App\Entity\Service;
use App\Entity\User;
use App\Enum\EmailConfirmation\EmailConfirmationStatus;
use App\Enum\EmailConfirmation\EmailConfirmationType;
use App\Enum\EmailType;
use App\Enum\Reservation\ReservationStatus;
use App\Enum\Reservation\ReservationType;
use App\Exceptions\ConflictException;
use App\Exceptions\VerifyEmailConfirmationException;
use App\Message\EmailConfirmationMessage;
use App\Message\EmailMessage;
use App\Message\ReservationVerificationMessage;
use App\Repository\EmailConfirmationRepository;
use App\Repository\ReservationRepository;
use App\Service\Entity\ReservationService;
use App\Service\EntitySerializer\EntitySerializerInterface;
use App\Service\Entity\EmailConfirmationService;
use App\Service\EmailConfirmation\EmailConfirmationHandlerInterface;
use App\Service\Entity\AvailabilityService;
use App\Tests\Unit\Service\Entity\DataProvider\ReservationServiceDataProvider;
use DateInterval;
use DateTime;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\MessageBusInterface;
use DateTimeImmutable;
use DateTimeInterface;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use Symfony\Component\Messenger\Envelope;

class ReservationServiceTest extends TestCase
{
    private EntitySerializerInterface&MockObject $entitySerializerMock;
    private AvailabilityService&MockObject $availabilityServiceMock;
    private EmailConfirmationService&MockObject $emailConfirmationServiceMock;
    private ReservationRepository&MockObject $reservationRepositoryMock;
    private EmailConfirmationRepository&MockObject $emailConfirmationRepositoryMock;
    private EmailConfirmationHandlerInterface&MockObject $emailConfirmationHandlerMock;
    private MessageBusInterface&MockObject $messageBusMock;
    private ReservationService $service;

    protected function setUp(): void
    {
        $this->entitySerializerMock = $this->createMock(EntitySerializerInterface::class);
        $this->availabilityServiceMock = $this->createMock(AvailabilityService::class);
        $this->emailConfirmationServiceMock = $this->createMock(EmailConfirmationService::class);
        $this->reservationRepositoryMock = $this->createMock(ReservationRepository::class);
        $this->emailConfirmationRepositoryMock = $this->createMock(EmailConfirmationRepository::class);
        $this->emailConfirmationHandlerMock = $this->createMock(EmailConfirmationHandlerInterface::class);
        $this->messageBusMock = $this->createMock(MessageBusInterface::class);

        $this->service = new ReservationService(
            $this->entitySerializerMock,
            $this->availabilityServiceMock,
            $this->emailConfirmationServiceMock,
            $this->reservationRepositoryMock,
            $this->emailConfirmationRepositoryMock,
            $this->emailConfirmationHandlerMock,
            $this->messageBusMock,
        );
    }

    public function testCreateReservationSuccess(): void
    {
        $dto = new ReservationCreateDTO(1, 2, 'user@example.com', '+48213721372', '2025-10-20T10:00+00:00', 'test');
        $startDateTime = new DateTimeImmutable('2025-10-20 10:00');
        $endDateTime = new DateTimeImmutable('2025-10-20 11:00');
        $expiryDate = new DateTimeImmutable('+ 30 minutes');
        $organizationMock = $this->prepareOrganizationMock();
        $serviceMock = $this->prepareServiceMock();
        $scheduleMock = $this->prepareScheduleMock($organizationMock);
        $reservationMock = $this->prepareReservationMock($scheduleMock, $serviceMock, $dto->email, $startDateTime, $endDateTime, $expiryDate);
        $verificationEmailConfirmationMock = $this->prepareEmailConfirmationMock(123, EmailConfirmationType::RESERVATION_VERIFICATION, $expiryDate);
        $cancellationEmailConfirmationMock = $this->prepareEmailConfirmationMock(124, EmailConfirmationType::RESERVATION_CANCELLATION, $startDateTime);
        $templateData = $this->prepareReservationVerificationTemplateParams($reservationMock, $startDateTime, $organizationMock, $serviceMock, $expiryDate);

        $this->entitySerializerMock
            ->method('parseToEntity')
            ->with($dto, Reservation::class)
            ->willReturn($reservationMock);

        $reservationMock->expects($this->once())->method('setEndDateTime')->with($startDateTime->add($serviceMock->getDuration()));
        $reservationMock->expects($this->once())->method('setOrganization')->with($organizationMock);
        $reservationMock->expects($this->once())->method('setEstimatedPrice')->with($serviceMock->getEstimatedPrice());
        $reservationMock->expects($this->once())->method('setStatus')->with(ReservationStatus::PENDING->value);
        $reservationMock->expects($this->once())->method('setType')->with(ReservationType::REGULAR->value);
        $reservationMock->expects($this->once())->method('setVerified')->with(false);
        $reservationMock->expects($this->once())->method('setExpiryDate')->with($this->isInstanceOf(DateTimeInterface::class));
        $callNr = 0;
        $reservationMock->expects($this->exactly(2))->method('addEmailConfirmation')->with(
            $this->callback(function($arg) use (&$callNr, $verificationEmailConfirmationMock, $cancellationEmailConfirmationMock){
                $callNr++;
                return match($callNr){
                    1 => $arg === $verificationEmailConfirmationMock,
                    2 => $arg === $cancellationEmailConfirmationMock,
                };
            })
        );

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

        $this->emailConfirmationServiceMock
            ->method('createEmailConfirmation')
            ->willReturnMap([
                [
                    $dto->email, 
                    $dto->verificationHandler, 
                    EmailConfirmationType::RESERVATION_VERIFICATION->value, 
                    null, 
                    $expiryDate,
                    $verificationEmailConfirmationMock
                ],
                [
                    $dto->email, 
                    $dto->verificationHandler, 
                    EmailConfirmationType::RESERVATION_CANCELLATION->value, 
                    null, 
                    $startDateTime,
                    $cancellationEmailConfirmationMock
                ]
            ]);

        $this->emailConfirmationHandlerMock
            ->method('generateSignedUrl')
            ->willReturnMap([
                [$verificationEmailConfirmationMock, $templateData['verification_url']],
                [$cancellationEmailConfirmationMock, $templateData['cancellation_url']],
            ]);

        $this->messageBusMock
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(fn($arg) => 
                $arg instanceof ReservationVerificationMessage &&
                $arg->getCancellationEmailConfirmationId() == $cancellationEmailConfirmationMock->getId() &&
                $arg->getVerificationEmailConfirmationId() == $verificationEmailConfirmationMock->getId() &&
                $arg->getReservationId() == $reservationMock->getId() &&
                $arg->getEmailType() == EmailType::RESERVATION_VERIFICATION->value && 
                $arg->getEmail() == $dto->email && 
                $arg->getTemplateParams() == $templateData
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
        $expiryDate = new DateTimeImmutable('+ 30 minutes');
        $organizationMock = $this->prepareOrganizationMock();
        $serviceMock = $this->prepareServiceMock();
        $scheduleMock = $this->prepareScheduleMock($organizationMock);
        $reservationMock = $this->prepareReservationMock($scheduleMock, $serviceMock, $dto->email, $startDateTime, $endDateTime, $expiryDate);

        $this->entitySerializerMock
            ->method('parseToEntity')
            ->with($dto, Reservation::class)
            ->willReturn($reservationMock);

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

    public function testCreateUserReservationSuccess(): void
    {
        $dto = new UserReservationCreateDTO(1, 2, '+48213721372', '2025-10-20T10:00+00:00', 'test');
        $userMock = $this->createMock(User::class);
        $userMock->method('getEmail')->willReturn('user@example.com');
        $startDateTime = new DateTimeImmutable('2025-10-20 10:00');
        $endDateTime = new DateTimeImmutable('2025-10-20 11:00');
        $organizationMock = $this->prepareOrganizationMock();
        $serviceMock = $this->prepareServiceMock();
        $scheduleMock = $this->prepareScheduleMock($organizationMock);
        $reservationMock = $this->prepareReservationMock($scheduleMock, $serviceMock, $userMock->getEmail(), $startDateTime, $endDateTime);
        $cancellationEmailConfirmationMock = $this->prepareEmailConfirmationMock(124, EmailConfirmationType::RESERVATION_CANCELLATION, $startDateTime);
        $templateData = $this->prepareReservationNotificationTemplateParams($reservationMock, $startDateTime, $organizationMock, $serviceMock);

        $reservationMock->expects($this->once())->method('setEndDateTime')->with($endDateTime);
        $reservationMock->expects($this->once())->method('setOrganization')->with($organizationMock);
        $reservationMock->expects($this->once())->method('setEstimatedPrice')->with($serviceMock->getEstimatedPrice());
        $reservationMock->expects($this->once())->method('setStatus')->with(ReservationStatus::PENDING->value);
        $reservationMock->expects($this->once())->method('setType')->with(ReservationType::REGULAR->value);
        $reservationMock->expects($this->once())->method('setReservedBy')->with($userMock);
        $lastVerifiedState = false;
        $reservationMock->method('setVerified')->willReturnCallback(function($arg) use (&$lastVerifiedState, $reservationMock){
            $lastVerifiedState = $arg;
            return $reservationMock;
        });

        $reservationMock->expects($this->once())->method('addEmailConfirmation')->with($cancellationEmailConfirmationMock);

        $this->entitySerializerMock
            ->method('parseToEntity')
            ->with(
                $this->callback(fn($arg) => 
                    $arg instanceof ReservationCreateDTO && 
                    $arg->scheduleId == $dto->scheduleId && 
                    $arg->serviceId == $dto->serviceId &&
                    $arg->email == $userMock->getEmail() && 
                    $arg->phoneNumber == $dto->phoneNumber && 
                    $arg->startDateTime == $dto->startDateTime && 
                    $arg->verificationHandler == $dto->verificationHandler
                ), 
                Reservation::class)
            ->willReturn($reservationMock);

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

        $this->emailConfirmationServiceMock
            ->method('createEmailConfirmation')
            ->with(
                $userMock->getEmail(), 
                $dto->verificationHandler, 
                EmailConfirmationType::RESERVATION_CANCELLATION->value, 
                null, 
                $startDateTime,
            )
            ->willReturn($cancellationEmailConfirmationMock);

        $this->emailConfirmationHandlerMock
            ->method('generateSignedUrl')
            ->with($cancellationEmailConfirmationMock)
            ->willReturn($templateData['cancellation_url']);

        $this->messageBusMock
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(fn($arg) => 
                $arg instanceof EmailConfirmationMessage &&
                $arg->getEmailConfirmationId() == $cancellationEmailConfirmationMock->getId() &&
                $arg->getEmailType() == EmailType::RESERVATION_SUMMARY->value && 
                $arg->getEmail() == $userMock->getEmail() && 
                $arg->getTemplateParams() == $templateData
            ))
            ->willReturn(new Envelope($this->createMock(EmailConfirmationMessage::class)));

        $result = $this->service->createUserReservation($dto, $userMock);
        $this->assertSame($reservationMock, $result);
        $this->assertTrue($lastVerifiedState);
    }

    public function testCreateUserReservationThrowsConflictWhenUnavailableTimeSlot(): void
    {
        $dto = new UserReservationCreateDTO(1, 2, '+48213721372', '2025-10-20T10:00+00:00', 'test');
        $userMock = $this->createMock(User::class);
        $userMock->method('getEmail')->willReturn('user@example.com');
        $startDateTime = new DateTimeImmutable('2025-10-20 10:00');
        $endDateTime = new DateTimeImmutable('2025-10-20 11:00');
        $organizationMock = $this->prepareOrganizationMock();
        $serviceMock = $this->prepareServiceMock();
        $scheduleMock = $this->prepareScheduleMock($organizationMock);
        $reservationMock = $this->prepareReservationMock($scheduleMock, $serviceMock, $userMock->getEmail(), $startDateTime, $endDateTime);

        $this->entitySerializerMock
            ->method('parseToEntity')
            ->with(
                $this->callback(fn($arg) => 
                    $arg instanceof ReservationCreateDTO && 
                    $arg->scheduleId == $dto->scheduleId && 
                    $arg->serviceId == $dto->serviceId &&
                    $arg->email == $userMock->getEmail() && 
                    $arg->phoneNumber == $dto->phoneNumber && 
                    $arg->startDateTime == $dto->startDateTime && 
                    $arg->verificationHandler == $dto->verificationHandler
                ), 
                Reservation::class)
            ->willReturn($reservationMock);

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

        $this->service->createUserReservation($dto, $userMock);
    }

    public function testVerifyReservationSucceeds(): void
    {
        $dto = new ReservationVerifyDTO(1, (new DateTime('+30 minutes'))->getTimestamp(), 'type', 'token', 'signature');

        $reservationMock = $this->createMock(Reservation::class);
        $reservationMock->method('getStatus')->willReturn(ReservationStatus::PENDING->value);
        $emailConfirmationMock = $this->createMock(EmailConfirmation::class);

        $this->emailConfirmationServiceMock->method('resolveEmailConfirmation')->with(
            $dto->id,
            $dto->token,
            $dto->_hash,
            $dto->expires,
            $dto->type
        )->willReturn($emailConfirmationMock);

        $this->reservationRepositoryMock
            ->method('findEmailConfirmationReservation')
            ->with($emailConfirmationMock)
            ->willReturn($reservationMock);

        $reservationMock->expects($this->once())->method('setVerified')->with(true);
        $reservationMock->expects($this->once())->method('setExpiryDate')->with(null);

        $emailConfirmationMock->expects($this->once())->method('setStatus')->with(EmailConfirmationStatus::COMPLETED->value);
        $this->emailConfirmationRepositoryMock->expects($this->once())->method('save')->with($emailConfirmationMock);
        $this->reservationRepositoryMock->expects($this->once())->method('save')->with($reservationMock, true);

        $result = $this->service->verifyReservation($dto);

        $this->assertTrue($result);
    }

    #[DataProviderExternal(ReservationServiceDataProvider::class, 'verifyReservationConflictDataCases')]
    public function testVerifyReservationThrowsConflict(bool $reservationExists, ReservationStatus $reservationStatus): void
    {
        $dto = new ReservationVerifyDTO(1, (new DateTime('+30 minutes'))->getTimestamp(), 'type', 'token', 'signature');

        $reservationMock = $this->createMock(Reservation::class);
        $reservationMock->method('getStatus')->willReturn($reservationStatus->value);
        $emailConfirmationMock = $this->createMock(EmailConfirmation::class);

        $this->emailConfirmationServiceMock->method('resolveEmailConfirmation')->with(
            $dto->id,
            $dto->token,
            $dto->_hash,
            $dto->expires,
            $dto->type
        )->willReturn($emailConfirmationMock);

        $this->reservationRepositoryMock
            ->method('findEmailConfirmationReservation')
            ->with($emailConfirmationMock)
            ->willReturn($reservationExists ? $reservationMock : null);

        $emailConfirmationMock->expects($this->once())->method('setStatus')->with(EmailConfirmationStatus::COMPLETED->value);
        $this->emailConfirmationRepositoryMock->expects($this->once())->method('save')->with($emailConfirmationMock);
        $this->emailConfirmationRepositoryMock->expects($this->once())->method('flush');
        $reservationMock->expects($this->never())->method('setVerified');
        $reservationMock->expects($this->never())->method('setExpiryDate');
        
        $this->reservationRepositoryMock->expects($this->never())->method('save');
        $this->expectException(ConflictException::class);

        $result = $this->service->verifyReservation($dto);

        $this->assertTrue($result);
    }

    public function testVerifyReservationFails(): void
    {
        $dto = new ReservationVerifyDTO(1, (new DateTime('+30 minutes'))->getTimestamp(), 'type', 'token', 'signature');

        $this->emailConfirmationServiceMock
            ->method('resolveEmailConfirmation')
            ->with(
                $dto->id,
                $dto->token,
                $dto->_hash,
                $dto->expires,
                $dto->type
            )
            ->willThrowException(new VerifyEmailConfirmationException());

        $this->assertFalse($this->service->verifyReservation($dto));
    }

    public function testCancelReservationByUrlSucceeds(): void
    {
        $startDateTime = new DateTimeImmutable('2025-10-20 10:00');
        $endDateTime = new DateTimeImmutable('2025-10-20 11:00');
        $dto = new ReservationUrlCancelDTO(1, $startDateTime->getTimestamp(), 'type', 'token', 'signature');
        $email = 'user@example.com';
        $organizationMock = $this->prepareOrganizationMock();
        $serviceMock = $this->prepareServiceMock();
        $scheduleMock = $this->prepareScheduleMock($organizationMock);
        $reservationMock = $this->prepareReservationMock($scheduleMock, $serviceMock, $email, $startDateTime, $endDateTime);
        $reservationMock->method('getStatus')->willReturn(ReservationStatus::CONFIRMED->value);
        $emailConfirmationMock = $this->createMock(EmailConfirmation::class);
        $templateData = $this->prepareReservationCancellationTemplateParams($reservationMock, $startDateTime, $organizationMock, $serviceMock);

        $this->emailConfirmationServiceMock->method('resolveEmailConfirmation')->with(
            $dto->id,
            $dto->token,
            $dto->_hash,
            $dto->expires,
            $dto->type
        )->willReturn($emailConfirmationMock);

        $this->reservationRepositoryMock
            ->method('findEmailConfirmationReservation')
            ->with($emailConfirmationMock)
            ->willReturn($reservationMock);

        $reservationMock->expects($this->once())->method('setStatus')->with(ReservationStatus::CUSTOMER_CANCELLED->value);

        $emailConfirmationMock->expects($this->once())->method('setStatus')->with(EmailConfirmationStatus::COMPLETED->value);
        $this->emailConfirmationRepositoryMock->expects($this->once())->method('save')->with($emailConfirmationMock);
        $this->reservationRepositoryMock->expects($this->once())->method('save')->with($reservationMock, true);

        $this->messageBusMock
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(fn($arg) => 
                $arg instanceof EmailMessage &&
                $arg->getEmailType() == EmailType::RESERVATION_CANCELLED_NOTIFICATION->value && 
                $arg->getEmail() == $email && 
                $arg->getTemplateParams() == $templateData
            ))
            ->willReturn(new Envelope($this->createMock(EmailConfirmationMessage::class)));

        $result = $this->service->cancelReservationByUrl($dto);

        $this->assertTrue($result);
    }

    #[DataProviderExternal(ReservationServiceDataProvider::class, 'cancelReservationByUrlConflictDataCases')]
    public function testCancelReservationByUrlThrowsConflict(bool $reservationExists, ReservationStatus $reservationStatus): void
    {
        $startDateTime = new DateTimeImmutable('2025-10-20 10:00');
        $dto = new ReservationUrlCancelDTO(1, $startDateTime->getTimestamp(), 'type', 'token', 'signature');

        $reservationMock = $this->createMock(Reservation::class);
        $reservationMock->method('getStatus')->willReturn($reservationStatus->value);
        $emailConfirmationMock = $this->createMock(EmailConfirmation::class);

        $this->emailConfirmationServiceMock->method('resolveEmailConfirmation')->with(
            $dto->id,
            $dto->token,
            $dto->_hash,
            $dto->expires,
            $dto->type
        )->willReturn($emailConfirmationMock);

        $this->reservationRepositoryMock
            ->method('findEmailConfirmationReservation')
            ->with($emailConfirmationMock)
            ->willReturn($reservationExists ? $reservationMock : null);

        $emailConfirmationMock->expects($this->once())->method('setStatus')->with(EmailConfirmationStatus::COMPLETED->value);
        $this->emailConfirmationRepositoryMock->expects($this->once())->method('save')->with($emailConfirmationMock);
        $this->emailConfirmationRepositoryMock->expects($this->once())->method('flush');
        $reservationMock->expects($this->never())->method('setStatus');
        
        $this->reservationRepositoryMock->expects($this->never())->method('save');
        $this->expectException(ConflictException::class);

        $result = $this->service->cancelReservationByUrl($dto);

        $this->assertTrue($result);
    }

    public function testCancelReservationByUrlFails(): void
    {
        $startDateTime = new DateTimeImmutable('2025-10-20 10:00');
        $dto = new ReservationUrlCancelDTO(1, $startDateTime->getTimestamp(), 'type', 'token', 'signature');

        $this->emailConfirmationServiceMock
            ->method('resolveEmailConfirmation')
            ->with(
                $dto->id,
                $dto->token,
                $dto->_hash,
                $dto->expires,
                $dto->type
            )
            ->willThrowException(new VerifyEmailConfirmationException());

        $this->assertFalse($this->service->cancelReservationByUrl($dto));
    }

    #[DataProviderExternal(ReservationServiceDataProvider::class, 'cancelOrganizationReservationDataCases')]
    public function testCancelOrganizationReservation(bool $notifyCustomer): void
    {
        $startDateTime = new DateTimeImmutable('2025-10-20 10:00');
        $endDateTime = new DateTimeImmutable('2025-10-20 11:00');
        $dto = new ReservationOrganizationCancelDTO($notifyCustomer);
        $email = 'user@example.com';
        $organizationMock = $this->prepareOrganizationMock();
        $serviceMock = $this->prepareServiceMock();
        $scheduleMock = $this->prepareScheduleMock($organizationMock);
        $reservationMock = $this->prepareReservationMock($scheduleMock, $serviceMock, $email, $startDateTime, $endDateTime);
        $templateData = $this->prepareReservationCancellationTemplateParams($reservationMock, $startDateTime, $organizationMock, $serviceMock);

        $reservationMock->expects($this->once())->method('setStatus')->with(ReservationStatus::ORGANIZATION_CANCELLED->value);
        $this->reservationRepositoryMock->expects($this->once())->method('save')->with($reservationMock, true);

        $this->messageBusMock
            ->expects($notifyCustomer ? $this->once() : $this->never())
            ->method('dispatch')
            ->with($this->callback(fn($arg) => 
                $arg instanceof EmailMessage &&
                $arg->getEmailType() == EmailType::RESERVATION_CANCELLED_NOTIFICATION->value && 
                $arg->getEmail() == $email && 
                $arg->getTemplateParams() == $templateData
            ))
            ->willReturn(new Envelope($this->createMock(EmailConfirmationMessage::class)));

        $this->service->cancelOrganizationReservation($reservationMock, $dto);
    }

    public function testConfirmReservation(): void
    {
        $dto = new ReservationConfirmDTO('test');
        $userEmail = 'user@example';
        $startDateTime = new DateTimeImmutable('2025-10-20 10:00');
        $endDateTime = new DateTimeImmutable('2025-10-20 11:00');
        $expiryDate = new DateTimeImmutable('+ 30 minutes');
        $organizationMock = $this->prepareOrganizationMock();
        $serviceMock = $this->prepareServiceMock();
        $scheduleMock = $this->prepareScheduleMock($organizationMock);
        $reservationMock = $this->prepareReservationMock($scheduleMock, $serviceMock, $userEmail, $startDateTime, $endDateTime, $expiryDate);
        $cancellationEmailConfirmationMock = $this->prepareEmailConfirmationMock(124, EmailConfirmationType::RESERVATION_CANCELLATION, $startDateTime);
        $templateData = $this->prepareReservationNotificationTemplateParams($reservationMock, $startDateTime, $organizationMock, $serviceMock);

        $reservationMock->expects($this->once())->method('setStatus')->with(ReservationStatus::CONFIRMED->value);
        $reservationMock->expects($this->once())->method('addEmailConfirmation')->with($cancellationEmailConfirmationMock);

        $this->reservationRepositoryMock
            ->expects($this->once())
            ->method('save')
            ->with($reservationMock, true);

        $this->emailConfirmationServiceMock
            ->method('createEmailConfirmation')
            ->with(
                $userEmail, 
                $dto->verificationHandler, 
                EmailConfirmationType::RESERVATION_CANCELLATION->value, 
                null, 
                $startDateTime,
            )
            ->willReturn($cancellationEmailConfirmationMock);

        $this->emailConfirmationHandlerMock
            ->method('generateSignedUrl')
            ->with($cancellationEmailConfirmationMock)
            ->willReturn($templateData['cancellation_url']);

        $this->messageBusMock
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(fn($arg) => 
                $arg instanceof EmailConfirmationMessage &&
                $arg->getEmailConfirmationId() == $cancellationEmailConfirmationMock->getId() &&
                $arg->getEmailType() == EmailType::RESERVATION_CONFIRMATION->value && 
                $arg->getEmail() == $userEmail && 
                $arg->getTemplateParams() == $templateData
            ))
            ->willReturn(new Envelope($this->createMock(ReservationVerificationMessage::class)));

        $this->service->confirmReservation($reservationMock, $dto);
    }

    #[DataProviderExternal(ReservationServiceDataProvider::class, 'cancelOrganizationReservationDataCases')]
    public function testPatchReservation(bool $notifyCustomer): void
    {
        $dto = new ReservationPatchDTO(1, 2, '+48213721372', 'user@example.com', '25.50', '2025-10-20T10:00+00:00', '2025-10-20T11:00+00:00', ReservationStatus::CONFIRMED->value, $notifyCustomer, 'test');
        $startDateTime = new DateTimeImmutable('2025-10-20 10:00');
        $endDateTime = new DateTimeImmutable('2025-10-20 11:00');
        $organizationMock = $this->prepareOrganizationMock();
        $serviceMock = $this->prepareServiceMock();
        $scheduleMock = $this->prepareScheduleMock($organizationMock);
        $reservationMock = $this->prepareReservationMock($scheduleMock, $serviceMock, $dto->email, $startDateTime, $endDateTime);
        $cancellationEmailConfirmationMock = $this->prepareEmailConfirmationMock(124, EmailConfirmationType::RESERVATION_CANCELLATION, $startDateTime);
        $templateData = $this->prepareReservationNotificationTemplateParams($reservationMock, $startDateTime, $organizationMock, $serviceMock);

        $this->entitySerializerMock
            ->method('parseToEntity')
            ->with($dto, $reservationMock)
            ->willReturn($reservationMock);

        $this->reservationRepositoryMock
            ->expects($this->once())
            ->method('save')
            ->with($reservationMock, true);

        $this->emailConfirmationServiceMock
            ->method('createEmailConfirmation')
            ->with(
                $dto->email, 
                $dto->verificationHandler, 
                EmailConfirmationType::RESERVATION_CANCELLATION->value, 
                null, 
                $startDateTime,
            )
            ->willReturn($cancellationEmailConfirmationMock);
        
        $reservationMock->expects($notifyCustomer ? $this->once() : $this->never())->method('addEmailConfirmation')->with($cancellationEmailConfirmationMock);

        $this->emailConfirmationHandlerMock
            ->method('generateSignedUrl')
            ->with($cancellationEmailConfirmationMock)
            ->willReturn($templateData['cancellation_url']);

        $this->messageBusMock
            ->expects($notifyCustomer ? $this->once() : $this->never())
            ->method('dispatch')
            ->with($this->callback(fn($arg) => 
                $arg instanceof EmailConfirmationMessage &&
                $arg->getEmailConfirmationId() == $cancellationEmailConfirmationMock->getId() &&
                $arg->getEmailType() == EmailType::RESERVATION_UPDATED_NOTIFICATION->value && 
                $arg->getEmail() == $dto->email && 
                $arg->getTemplateParams() == $templateData
            ))
            ->willReturn(new Envelope($this->createMock(EmailConfirmationMessage::class)));

        $result = $this->service->patchReservation($reservationMock, $dto);
        $this->assertSame($reservationMock, $result);
    }

    private function prepareReservationVerificationTemplateParams(
        Reservation $reservation, 
        DateTimeInterface $startDateTime, 
        Organization $organization,
        Service $service,
        DateTimeInterface $expiryDate,
    ): array
    {
        return [
            'reference' => $reservation->getReference(),
            'verification_url' => 'api/reservation/verify',
            'verification_expiration_date' => $expiryDate,
            'cancellation_url' => 'api/reservation/cancel',
            'cancellation_expiration_date' => $startDateTime,
            'organization_name' => $organization->getName(),
            'service_name' => $service->getName(),
            'start_date_time' => $startDateTime,
            'estimated_price' => '25.50',
            'duration' => $service->getDuration()->format('%h:%ih'),
        ];
    }

    private function prepareReservationNotificationTemplateParams(
        Reservation $reservation, 
        DateTimeInterface $startDateTime, 
        Organization $organization,
        Service $service,
    ): array
    {
        return [
            'reference' => $reservation->getReference(),
            'cancellation_url' => 'api/reservation/cancel',
            'cancellation_expiration_date' => $startDateTime,
            'organization_name' => $organization->getName(),
            'service_name' => $service->getName(),
            'start_date_time' => $startDateTime,
            'estimated_price' => '25.50',
            'duration' => $service->getDuration()->format('%h:%ih'),
        ];
    }

    private function prepareReservationCancellationTemplateParams(
        Reservation $reservation, 
        DateTimeInterface $startDateTime, 
        Organization $organization,
        Service $service,
    ): array
    {
        return [
            'reference' => $reservation->getReference(),
            'organization_name' => $organization->getName(),
            'service_name' => $service->getName(),
            'start_date_time' => $startDateTime,
            'estimated_price' => '25.50',
            'duration' => $service->getDuration()->format('%h:%ih'),
        ];
    }

    private function prepareReservationMock(
        Schedule&MockObject $scheduleMock,
        Service&MockObject $serviceMock,
        string $userEmail,
        DateTimeImmutable $startDateTime,
        DateTimeImmutable $endDateTime,
        ?DateTimeInterface $expiryDate = null,
    ): Reservation&MockObject
    {
        $reservationMock = $this->createMock(Reservation::class);
        $reservationMock->method('getId')->willReturn(1);
        $reservationMock->method('getReference')->willReturn('ref');
        $reservationMock->method('getOrganization')->willReturn($scheduleMock->getOrganization());
        $reservationMock->method('getService')->willReturn($serviceMock);
        $reservationMock->method('getSchedule')->willReturn($scheduleMock);
        $reservationMock->method('getStartDateTime')->willReturn($startDateTime);
        $reservationMock->method('getEndDateTime')->willReturn($endDateTime);
        $reservationMock->method('getEmail')->willReturn($userEmail);
        $reservationMock->method('getEstimatedPrice')->willReturn($serviceMock->getEstimatedPrice());
        $reservationMock->method('getExpiryDate')->willReturn($expiryDate);

        return $reservationMock;
    }

    private function prepareServiceMock(): Service&MockObject
    {
        $serviceMock = $this->createMock(Service::class);
        $serviceMock->method('getId')->willReturn(2);
        $serviceMock->method('getDuration')->willReturn(new DateInterval('PT1H'));
        $serviceMock->method('getEstimatedPrice')->willReturn('25.50');
        $serviceMock->method('getName')->willReturn('Test service');

        return $serviceMock;
    }

    private function prepareOrganizationMock(): Organization&MockObject
    {
        $organizationMock = $this->createMock(Organization::class);
        $organizationMock->method('getName')->willReturn('Test Organization');

        return $organizationMock;
    }

    private function prepareScheduleMock(Organization&MockObject $organizationMock): Schedule&MockObject
    {
        $scheduleMock = $this->createMock(Schedule::class);
        $scheduleMock->method('getId')->willReturn(3);
        $scheduleMock->method('getOrganization')->willReturn($organizationMock);

        return $scheduleMock;
    }

    private function prepareEmailConfirmationMock(int $id, EmailConfirmationType $type, DateTimeInterface $expiryDate): EmailConfirmation&MockObject
    {
        $emailConfirmationMock = $this->createMock(EmailConfirmation::class);
        $emailConfirmationMock->method('getId')->willReturn($id);
        $emailConfirmationMock->method('getType')->willReturn($type->value);
        $emailConfirmationMock->method('getExpiryDate')->willReturn($expiryDate);

        return $emailConfirmationMock;
    }
}
