<?php

declare(strict_types=1);

namespace App\Service\Entity;

use App\DTO\Reservation\ReservationCreateDTO;
use App\DTO\Reservation\UserReservationCreateDTO;
use App\Entity\EmailConfirmation;
use App\Service\EntitySerializer\EntitySerializerInterface;
use App\Entity\Reservation;
use App\Entity\User;
use App\Enum\EmailConfirmation\EmailConfirmationType;
use App\Enum\EmailType;
use App\Enum\Reservation\ReservationStatus;
use App\Enum\Reservation\ReservationType;
use App\Exceptions\ConflictException;
use App\Message\EmailConfirmationMessage;
use App\Message\ReservationVerificationMessage;
use App\Repository\ReservationRepository;
use App\Service\EmailConfirmation\EmailConfirmationHandlerInterface;
use DateTime;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Messenger\MessageBusInterface;

class ReservationService
{
    const DEFAULT_RESERVATION_VERIFICATION_EXPIRY = '+30 minutes';

    public function __construct(
        private EntitySerializerInterface $entitySerializer,
        private AvailabilityService $availabilityService,
        private EmailConfirmationService $emailConfirmationService,
        private ReservationRepository $reservationRepository,
        private EmailConfirmationHandlerInterface $emailConfirmationHandler,
        private MessageBusInterface $messageBus,
    )
    {

    }

    public function createReservation(ReservationCreateDTO $dto): Reservation
    {
        $reservation = $this->makeReservation($dto);
        $this->validateReservationAvailability($reservation);
        
        $reservation->setExpiryDate(new DateTime(self::DEFAULT_RESERVATION_VERIFICATION_EXPIRY));
        
        $this->reservationRepository->save($reservation, true);
        $this->sendReservationVerification($reservation, $dto->verificationHandler);

        return $reservation;
    }

    public function createUserReservation(UserReservationCreateDTO $dto, User $user): Reservation
    {
        $reservation = $this->makeReservation(new ReservationCreateDTO(
            $dto->scheduleId,
            $dto->serviceId,
            $user->getEmail(),
            $dto->phoneNumber,
            $dto->startDateTime,
            $dto->verificationHandler
        ));
        $this->validateReservationAvailability($reservation);

        $reservation->setVerified(true);
        $reservation->setReservedBy($user);
        
        $this->reservationRepository->save($reservation, true);
        $this->sendReservationSummary($reservation, $dto->verificationHandler);

        return $reservation;
    }

    private function makeReservation(ReservationCreateDTO $dto): Reservation
    {
        $reservation = $this->entitySerializer->parseToEntity($dto, Reservation::class);
        $endDateTime = $reservation->getStartDateTime()->add($reservation->getService()->getDuration());
        $reference = $this->generateReservationReference($reservation);
        
        $reservation->setEndDateTime($endDateTime);
        $reservation->setOrganization($reservation->getSchedule()->getOrganization());
        $reservation->setReference($reference);
        $reservation->setEstimatedPrice($reservation->getService()->getEstimatedPrice());
        $reservation->setStatus(ReservationStatus::PENDING->value);
        $reservation->setType(ReservationType::REGULAR->value);
        $reservation->setVerified(false);

        return $reservation;
    }

    private function validateReservationAvailability(Reservation $reservation): void
    {
        $startDateTime = $reservation->getStartDateTime();
        $date = $startDateTime->format('Y-m-d');
        $time = $startDateTime->format('H:i');
        $availability = $this->availabilityService->getScheduleAvailability(
            $reservation->getSchedule(), 
            $reservation->getService(), 
            $startDateTime, 
            $reservation->getEndDateTime()
        );

        if(!isset($availability[$date]) || !in_array($time, $availability[$date])){
            throw new ConflictException("Reservation time slot is not available.");
        }
    }

    private function sendReservationVerification(Reservation $reservation, string $verificationHandler): void
    {
        $verificationEmailConfirmation = $this->createReservationVerification($reservation, $verificationHandler);
        $cancellationEmailConfirmation = $this->createReservationCancellation($reservation, $verificationHandler);

        $this->messageBus->dispatch(new ReservationVerificationMessage(
            $verificationEmailConfirmation->getId(),
            $reservation->getId(),
            EmailType::RESERVATION_VERIFICATION->value,
            $reservation->getEmail(),
            [
                'reference' => $reservation->getReference(),
                'verification_url' => $this->emailConfirmationHandler->generateSignedUrl($verificationEmailConfirmation),
                'verification_expiration_date' => $verificationEmailConfirmation->getExpiryDate(),
                'cancellation_url' => $this->emailConfirmationHandler->generateSignedUrl($cancellationEmailConfirmation),
                'cancellation_expiration_date' => $cancellationEmailConfirmation->getExpiryDate(),
                'organization_name' => $reservation->getOrganization()->getName(),
                'service_name' => $reservation->getService()->getName(),
                'start_date_time' => $reservation->getStartDateTime(),
                'estimated_price' => $reservation->getEstimatedPrice(),
                'duration' => $reservation->getService()->getDuration()->format('%h:%ih'),
            ]
        ));
    }

    private function sendReservationSummary(Reservation $reservation, string $verificationHandler): void
    {
        $cancellationEmailConfirmation = $this->createReservationCancellation($reservation, $verificationHandler);

        $this->messageBus->dispatch(new EmailConfirmationMessage(
            $cancellationEmailConfirmation->getId(),
            EmailType::RESERVATION_SUMMARY->value,
            $reservation->getEmail(),
            [
                'reference' => $reservation->getReference(),
                'cancellation_url' => $this->emailConfirmationHandler->generateSignedUrl($cancellationEmailConfirmation),
                'cancellation_expiration_date' => $cancellationEmailConfirmation->getExpiryDate(),
                'organization_name' => $reservation->getOrganization()->getName(),
                'service_name' => $reservation->getService()->getName(),
                'start_date_time' => $reservation->getStartDateTime(),
                'estimated_price' => $reservation->getEstimatedPrice(),
                'duration' => $reservation->getService()->getDuration()->format('%h:%ih'),
            ]
        ));
    }

    private function createReservationVerification(Reservation $reservation, string $verificationHandler): EmailConfirmation
    {
        return $this->emailConfirmationService->createEmailConfirmation(
            $reservation->getEmail(),
            $verificationHandler,
            EmailConfirmationType::RESERVATION_VERIFICATION->value,
            null,
            $reservation->getExpiryDate(),
            ['reservation_id' => $reservation->getId()]
        );
    }

    private function createReservationCancellation(Reservation $reservation, string $verificationHandler): EmailConfirmation
    {
        return $this->emailConfirmationService->createEmailConfirmation(
            $reservation->getEmail(),
            $verificationHandler,
            EmailConfirmationType::RESERVATION_CANCELLATION->value,
            null,
            $reservation->getStartDateTime(),
            ['reservation_id' => $reservation->getId()]
        );
    }

    private function generateReservationReference(Reservation $reservation): string
    {
        $uuid = Uuid::uuid4()->toString();
        $date = $reservation->getStartDateTime()->format('Y-m-d');
        $scheduleId = $reservation->getSchedule()->getId();
        $serviceId = $reservation->getService()->getId();

        return "RSV-$date{$scheduleId}{$serviceId}/".substr($uuid,0,8);
    }

}