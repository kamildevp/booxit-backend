<?php

declare(strict_types=1);

namespace App\Service\Entity;

use App\DTO\Reservation\ReservationCreateDTO;
use App\DTO\ScheduleReservation\ScheduleReservationConfirmDTO;
use App\DTO\ScheduleReservation\ScheduleReservationCreateCustomDTO;
use App\DTO\ScheduleReservation\ScheduleReservationCreateDTO;
use App\DTO\ScheduleReservation\ScheduleReservationPatchDTO;
use App\Service\EntitySerializer\EntitySerializerInterface;
use App\Entity\Reservation;
use App\Entity\Schedule;
use App\Enum\EmailType;
use App\Enum\Reservation\ReservationStatus;
use App\Enum\Reservation\ReservationType;
use App\Exceptions\ConflictException;
use App\Repository\ReservationRepository;
use DateTime;

class ScheduleReservationService
{
    public function __construct(
        private EntitySerializerInterface $entitySerializer,
        private ReservationRepository $reservationRepository,
        private ReservationService $reservationService,
    )
    {

    }

    public function createScheduleReservation(Schedule $schedule, ScheduleReservationCreateDTO $dto): Reservation
    {
        $reservation = $this->reservationService->makeReservation(new ReservationCreateDTO(
            $schedule->getId(),
            $dto->serviceId,
            $dto->email,
            $dto->phoneNumber,
            $dto->startDateTime
        ));
        $this->reservationService->validateReservationAvailability($reservation);
        
        $reservation->setExpiryDate(new DateTime(ReservationService::DEFAULT_RESERVATION_VERIFICATION_EXPIRY));
        
        $this->reservationRepository->save($reservation, true);
        $this->reservationService->sendReservationVerification($reservation, $dto->verificationHandler);

        return $reservation;
    }

    public function createCustomScheduleReservation(Schedule $schedule, ScheduleReservationCreateCustomDTO $dto): Reservation
    {
        $reservation = $this->entitySerializer->parseToEntity($dto, Reservation::class);
        $reservation->setSchedule($schedule);
        $reference = $this->reservationService->generateReservationReference($reservation);
        $reservation->setReference($reference);
        $reservation->setOrganization($reservation->getSchedule()->getOrganization());
        $reservation->setType(ReservationType::CUSTOM->value);
        $reservation->setVerified(true);
        $this->reservationRepository->save($reservation, true);

        return $reservation;
    }

    public function cancelScheduleReservation(Reservation $reservation): void
    {
        if(in_array($reservation->getStatus(), ReservationStatus::getCancelledStatuses())){
            throw new ConflictException('Reservation has already been cancelled.');
        }

        $reservation->setStatus(ReservationStatus::ORGANIZATION_CANCELLED->value);
        $this->reservationRepository->save($reservation, true);
        $this->reservationService->sendReservationCancelledNotification($reservation);
    }

    public function confirmScheduleReservation(Reservation $reservation, ScheduleReservationConfirmDTO $dto): void
    {
        if($reservation->getStatus() == ReservationStatus::CONFIRMED->value){
            throw new ConflictException('Reservation has already been confirmed.');
        }

        $reservation->setStatus(ReservationStatus::CONFIRMED->value);
        
        $this->reservationRepository->save($reservation, true);
        $this->reservationService->sendReservationNotification($reservation, $dto->verificationHandler, EmailType::RESERVATION_CONFIRMATION);
    }

    public function patchScheduleReservation(Reservation $reservation, ScheduleReservationPatchDTO $dto): Reservation
    {
        $reservation = $this->entitySerializer->parseToEntity($dto, $reservation);

        $this->reservationRepository->save($reservation, true);
        if($dto->notifyCustomer){
            $this->reservationService->sendReservationNotification($reservation, $dto->verificationHandler, EmailType::RESERVATION_UPDATED_NOTIFICATION);
        }
        
        return $reservation;
    }
}