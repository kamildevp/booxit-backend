<?php

declare(strict_types=1);

namespace App\Message;

class ReservationVerificationMessage extends EmailMessage
{
    public function __construct(
        private int $emailConfirmationId,
        private int $reservationId,
        string $emailType,
        string $email,
        array $templateParams
    ) 
    {
        parent::__construct($emailType, $email, $templateParams);
    }

    public function getEmailConfirmationId(): int
    {
        return $this->emailConfirmationId;
    }

    public function getReservationId(): int
    {
        return $this->reservationId;
    }
}