<?php

declare(strict_types=1);

namespace App\DTO\Reservation;

use App\DTO\AbstractDTO;
use App\DTO\EmailConfirmation\Trait\VerificationHandlerFieldDTO;

class ReservationConfirmDTO extends AbstractDTO 
{
    use VerificationHandlerFieldDTO;

    public function __construct(
        string $verificationHandler
    )
    {
        $this->verificationHandler = $verificationHandler;
    }
}