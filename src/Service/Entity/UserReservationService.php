<?php

declare(strict_types=1);

namespace App\Service\Entity;

use App\DTO\Reservation\ReservationCreateDTO;
use App\DTO\UserReservation\UserReservationCreateDTO;
use App\Entity\Reservation;
use App\Entity\User;
use App\Enum\EmailType;
use App\Enum\Reservation\ReservationStatus;
use App\Exceptions\ConflictException;
use App\Exceptions\EntityNotFoundException;
use App\Repository\ReservationRepository;

class UserReservationService
{
    const DEFAULT_RESERVATION_VERIFICATION_EXPIRY = '+30 minutes';

    public function __construct(
        private ReservationRepository $reservationRepository,
        private ReservationService $reservationService,
    )
    {

    }

    public function createUserReservation(UserReservationCreateDTO $dto, User $user): Reservation
    {
        $reservation = $this->reservationService->makeReservation(new ReservationCreateDTO(
            $dto->scheduleId,
            $dto->serviceId,
            $user->getEmail(),
            $dto->phoneNumber,
            $dto->startDateTime,
        ));
        $this->reservationService->validateReservationAvailability($reservation);

        $reservation->setVerified(true);
        $reservation->setReservedBy($user);
        
        $this->reservationRepository->save($reservation, true);
        $this->reservationService->sendReservationNotification($reservation, $dto->verificationHandler, EmailType::RESERVATION_SUMMARY);

        return $reservation;
    }

    public function cancelUserReservation(Reservation $reservation, User $user): void
    {
        if(!$user->hasReservation($reservation)){
            throw new EntityNotFoundException(Reservation::class);
        }

        if(in_array($reservation->getStatus(), ReservationStatus::getCancelledStatuses())){
            throw new ConflictException('Reservation has already been cancelled.');
        }

        $reservation->setStatus(ReservationStatus::CUSTOMER_CANCELLED->value);
        $this->reservationRepository->save($reservation, true);
        $this->reservationService->sendReservationCancelledNotification($reservation);
    }
}