<?php

declare(strict_types=1);

namespace App\Service\Entity;

use App\DTO\Reservation\ReservationCreateDTO;
use App\DTO\Reservation\ReservationUrlCancelDTO;
use App\DTO\Reservation\ReservationVerifyDTO;
use App\Entity\EmailConfirmation;
use App\Service\EntitySerializer\EntitySerializerInterface;
use App\Entity\Reservation;
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
use App\Service\EmailConfirmation\EmailConfirmationHandlerInterface;
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
        private EmailConfirmationRepository $emailConfirmationRepository,
        private EmailConfirmationHandlerInterface $emailConfirmationHandler,
        private MessageBusInterface $messageBus,
    )
    {

    }

    public function verifyReservation(ReservationVerifyDTO $dto): bool
    {
        $emailConfirmation = $this->resolveReservationEmailConfirmation($dto);
        if(!$emailConfirmation){
            return false;
        }

        $emailConfirmation->setStatus(EmailConfirmationStatus::COMPLETED->value);
        $this->emailConfirmationRepository->save($emailConfirmation);

        $reservation = $this->reservationRepository->findEmailConfirmationReservation($emailConfirmation);
        if(!$reservation || $reservation->getStatus() != ReservationStatus::PENDING->value){
            $this->emailConfirmationRepository->flush();
            throw new ConflictException('Corresponding reservation does not exist, has been cancelled or is already verified.');
        }

        $reservation->setVerified(true);
        $reservation->setExpiryDate(null);
        $this->reservationRepository->save($reservation, true);

        return true;
    }

    public function cancelReservationByUrl(ReservationUrlCancelDTO $dto): bool
    {
        $emailConfirmation = $this->resolveReservationEmailConfirmation($dto);
        if(!$emailConfirmation){
            return false;
        }

        $emailConfirmation->setStatus(EmailConfirmationStatus::COMPLETED->value);
        $this->emailConfirmationRepository->save($emailConfirmation);

        $reservation = $this->reservationRepository->findEmailConfirmationReservation($emailConfirmation);
        if(!$reservation || in_array($reservation->getStatus(), ReservationStatus::getCancelledStatuses())){
            $this->emailConfirmationRepository->flush();
            throw new ConflictException('Corresponding reservation does not exist or already has been cancelled.');
        }

        $reservation->setStatus(ReservationStatus::CUSTOMER_CANCELLED->value);
        $this->reservationRepository->save($reservation, true);
        $this->sendReservationCancelledNotification($reservation);

        return true;
    }

    public function makeReservation(ReservationCreateDTO $dto): Reservation
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

    public function validateReservationAvailability(Reservation $reservation): void
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

    public function sendReservationVerification(Reservation $reservation, string $verificationHandler): void
    {
        $verificationEmailConfirmation = $this->createReservationVerification($reservation, $verificationHandler);
        $cancellationEmailConfirmation = $this->createReservationCancellation($reservation, $verificationHandler);
        $this->reservationRepository->flush();

        $this->messageBus->dispatch(new ReservationVerificationMessage(
            $verificationEmailConfirmation->getId(),
            $cancellationEmailConfirmation->getId(),
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
                'duration' => $reservation->getService()->getDuration()->format('%H:%I h'),
            ],
            $reservation->getLanguagePreference(),
        ));
    }

    public function sendReservationNotification(Reservation $reservation, string $verificationHandler, EmailType $type): void
    {
        $cancellationEmailConfirmation = $this->createReservationCancellation($reservation, $verificationHandler);
        $this->reservationRepository->flush();

        $this->messageBus->dispatch(new EmailConfirmationMessage(
            $cancellationEmailConfirmation->getId(),
            $type->value,
            $reservation->getEmail(),
            [
                'reference' => $reservation->getReference(),
                'cancellation_url' => $this->emailConfirmationHandler->generateSignedUrl($cancellationEmailConfirmation),
                'cancellation_expiration_date' => $cancellationEmailConfirmation->getExpiryDate(),
                'organization_name' => $reservation->getOrganization()->getName(),
                'service_name' => $reservation->getService()->getName(),
                'start_date_time' => $reservation->getStartDateTime(),
                'estimated_price' => $reservation->getEstimatedPrice(),
                'duration' => $reservation->getService()->getDuration()->format('%H:%I h'),
            ],
            $reservation->getLanguagePreference(),
        ));
    }

    public function sendReservationCancelledNotification(Reservation $reservation): void
    {
        $this->messageBus->dispatch(new EmailMessage(
            EmailType::RESERVATION_CANCELLED_NOTIFICATION->value,
            $reservation->getEmail(),
            [
                'reference' => $reservation->getReference(),
                'organization_name' => $reservation->getOrganization()->getName(),
                'service_name' => $reservation->getService()->getName(),
                'start_date_time' => $reservation->getStartDateTime(),
                'estimated_price' => $reservation->getEstimatedPrice(),
                'duration' => $reservation->getService()->getDuration()->format('%H:%I h'),
            ],
            $reservation->getLanguagePreference(),
        ));
    }

    private function resolveReservationEmailConfirmation(ReservationVerifyDTO|ReservationUrlCancelDTO $dto): ?EmailConfirmation
    {
        try{
            return $this->emailConfirmationService->resolveEmailConfirmation(
                $dto->id,
                $dto->token,
                $dto->_hash,
                $dto->expires,
                $dto->type
            );
        }
        catch(VerifyEmailConfirmationException)
        {
            return null;
        }
    }

    private function createReservationVerification(Reservation $reservation, string $verificationHandler): EmailConfirmation
    {
        $emailConfirmation = $this->emailConfirmationService->createEmailConfirmation(
            $reservation->getEmail(),
            $verificationHandler,
            EmailConfirmationType::RESERVATION_VERIFICATION->value,
            null,
            $reservation->getExpiryDate(),
        );
    
        $reservation->addEmailConfirmation($emailConfirmation);
        return $emailConfirmation;
    }

    private function createReservationCancellation(Reservation $reservation, string $verificationHandler): EmailConfirmation
    {
        $emailConfirmation = $this->emailConfirmationService->createEmailConfirmation(
            $reservation->getEmail(),
            $verificationHandler,
            EmailConfirmationType::RESERVATION_CANCELLATION->value,
            null,
            $reservation->getStartDateTime(),
        );

        $reservation->addEmailConfirmation($emailConfirmation);
        return $emailConfirmation;
    }

    public function generateReservationReference(Reservation $reservation): string
    {
        $uuid = Uuid::uuid4()->toString();
        $date = $reservation->getStartDateTime()->format('Y-m-d');
        $scheduleId = $reservation->getSchedule()->getId();
        $serviceId = $reservation->getService()->getId();

        return "RSV-$date{$scheduleId}{$serviceId}/".substr($uuid,0,8);
    }

}