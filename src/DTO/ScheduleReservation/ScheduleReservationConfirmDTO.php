<?php

declare(strict_types=1);

namespace App\DTO\ScheduleReservation;

use App\DTO\AbstractDTO;
use App\DTO\EmailConfirmation\Trait\VerificationHandlerFieldDTO;

class ScheduleReservationConfirmDTO extends AbstractDTO 
{
    use VerificationHandlerFieldDTO;

    public function __construct(
        string $verificationHandler
    )
    {
        $this->verificationHandler = $verificationHandler;
    }
}