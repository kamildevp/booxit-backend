<?php

declare(strict_types=1);

namespace App\Message;

class ReservationVerificationMessage extends EmailMessage
{
    public function __construct(
        private int $verificationEmailConfirmationId,
        private int $cancellationEmailConfirmationId,
        private int $reservationId,
        string $emailType,
        string $email,
        array $templateParams
    ) 
    {
        parent::__construct($emailType, $email, $templateParams);
    }

    public function getVerificationEmailConfirmationId(): int
    {
        return $this->verificationEmailConfirmationId;
    }

    public function getCancellationEmailConfirmationId(): int
    {
        return $this->cancellationEmailConfirmationId;
    }

    public function getReservationId(): int
    {
        return $this->reservationId;
    }
}